<?php

namespace App\Services;

use App\Events\LowStockNotification;
use App\Events\SaleCompleted;
use App\Exceptions\NoActiveShiftException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PaymentMismatchException;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SaleService
{
    public function __construct(
        protected LoyaltyService $loyalty,
        protected ShiftService $shiftService,
        protected ReceiptService $receiptService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $cashier, array $data): Sale
    {
        $shift = $cashier->activeShift();

        if ($shift === null) {
            throw new NoActiveShiftException;
        }

        $cart = $this->buildCart($data['items']);

        $totals = $this->calculateTotals($cart, (float) ($data['discount'] ?? 0));

        $this->validatePayments($totals['total'], $data['payments'] ?? []);

        try {
            $sale = DB::transaction(function () use ($cashier, $shift, $data, $cart, $totals) {
                $sale = Sale::create([
                    'invoice_number' => $this->nextInvoiceNumber(),
                    'customer_id' => $data['customer_id'] ?? null,
                    'user_id' => $cashier->id,
                    'shift_id' => $shift->id,
                    'subtotal' => $totals['subtotal'],
                    'discount' => $totals['discount'],
                    'tax' => $totals['tax'],
                    'total' => $totals['total'],
                    'amount_paid' => $totals['paid'],
                    'change_due' => $totals['change'],
                    'payment_status' => Sale::STATUS_COMPLETED,
                    'payment_method' => $this->summarizeMethods($data['payments'] ?? []),
                    'notes' => $data['notes'] ?? null,
                    'sold_at' => now(),
                ]);

                foreach ($cart as $line) {
                    $sale->items()->create([
                        'product_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['price'],
                        'discount' => $line['discount'],
                        'tax_rate' => $line['tax_rate'],
                        'tax_amount' => $line['tax_amount'],
                        'total' => $line['total'],
                        'notes' => $line['notes'] ?? null,
                    ]);

                    $this->deductStock($line, $sale);
                }

                foreach ($data['payments'] ?? [] as $payment) {
                    $sale->payments()->create([
                        'method' => $payment['method'],
                        'amount' => $payment['amount'],
                        'reference' => $payment['reference'] ?? null,
                        'status' => 'completed',
                        'paid_at' => now(),
                    ]);
                }

                if (! empty($data['customer_id'])) {
                    $customer = Customer::find($data['customer_id']);
                    if ($customer !== null) {
                        $this->loyalty->earn($customer, $sale->id, $totals['total']);
                    }
                }

                $this->shiftService->refreshTotals($shift->fresh());
                $this->receiptService->generate($sale);

                AuditLog::record('sale_created', $sale, [
                    'total' => $totals['total'],
                    'items' => count($cart),
                ]);

                return $sale;
            });
        } catch (Throwable $e) {
            throw $e;
        }

        $sale->load(['items.product', 'payments', 'customer', 'receipt']);
        SaleCompleted::dispatch($sale);

        return $sale;
    }

    public function cancel(Sale $sale, ?string $reason = null): Sale
    {
        if (! $sale->isCompleted()) {
            throw new \InvalidArgumentException('Only completed sales can be cancelled.');
        }

        DB::transaction(function () use ($sale, $reason) {
            foreach ($sale->items as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)->first();

                if ($inventory !== null) {
                    $inventory->increment('quantity', $item->quantity);

                    InventoryMovement::create([
                        'product_id' => $item->product_id,
                        'type' => InventoryMovement::TYPE_IN,
                        'quantity' => $item->quantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'notes' => 'Restock from cancelled sale '.$sale->invoice_number,
                    ]);
                }
            }

            if ($sale->customer_id !== null && $sale->loyaltyTransactions()->where('type', 'earned')->exists()) {
                $earned = $sale->loyaltyTransactions()->where('type', 'earned')->sum('points');
                $sale->customer->decrement('loyalty_points', $earned);
            }

            $sale->payments()->update(['status' => 'refunded']);
            $sale->update([
                'payment_status' => Sale::STATUS_CANCELLED,
                'notes' => trim(($sale->notes ?? '')." | Cancelled: {$reason}"),
            ]);

            if ($sale->shift_id !== null) {
                $this->shiftService->refreshTotals($sale->shift);
            }

            AuditLog::record('sale_cancelled', $sale, ['reason' => $reason]);
        });

        return $sale->fresh(['items.product', 'payments', 'customer']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawItems
     * @return array<int, array<string, mixed>>
     */
    protected function buildCart(array $rawItems): array
    {
        $cart = [];

        foreach ($rawItems as $raw) {
            $product = Product::with('inventory')->find($raw['product_id']);

            if ($product === null) {
                throw new \InvalidArgumentException("Product #{$raw['product_id']} not found.");
            }

            $quantity = max(1, (int) ($raw['quantity'] ?? 1));
            $available = $product->inventory?->quantity ?? 0;

            if ($available < $quantity) {
                throw new OutOfStockException($product->name, $available);
            }

            $price = (float) $product->selling_price;
            $itemDiscount = (float) ($raw['discount'] ?? 0);
            $taxRate = (float) ($raw['tax_rate'] ?? $product->tax_rate);
            $lineNet = round($price - $itemDiscount, 2);
            $lineTotal = round($lineNet * $quantity, 2);
            $taxAmount = round($lineTotal * ($taxRate / 100), 2);

            $cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $price,
                'discount' => $itemDiscount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
                'total' => round($lineTotal + $taxAmount, 2),
                'notes' => $raw['notes'] ?? null,
            ];
        }

        return $cart;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cart
     * @return array<string, float|int>
     */
    protected function calculateTotals(array $cart, float $discount): array
    {
        $subtotal = round(array_sum(array_column($cart, 'line_total')), 2);
        $tax = round(array_sum(array_column($cart, 'tax_amount')), 2);

        if ($discount < 0 || $discount > $subtotal) {
            throw new \InvalidArgumentException('Cart discount must be between 0 and the subtotal.');
        }

        $total = round($subtotal - $discount + $tax, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'paid' => $total,
            'change' => 0.0,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $payments
     */
    protected function validatePayments(float $total, array $payments): void
    {
        if ($payments === []) {
            throw new PaymentMismatchException;
        }

        $sum = 0.0;

        foreach ($payments as $payment) {
            $sum += (float) ($payment['amount'] ?? 0);
        }

        if (abs($sum - $total) > 0.01) {
            throw new PaymentMismatchException(
                'The sum of payments ('.number_format($sum, 2).') does not match the sale total ('.number_format($total, 2).').'
            );
        }
    }

    /**
     * @param  array<string, mixed>  $line
     */
    protected function deductStock(array $line, Sale $sale): void
    {
        $inventory = Inventory::where('product_id', $line['product_id'])->first();

        if ($inventory === null) {
            throw new OutOfStockException($line['name']);
        }

        $inventory->decrement('quantity', $line['quantity']);

        InventoryMovement::create([
            'product_id' => $line['product_id'],
            'type' => InventoryMovement::TYPE_SALE,
            'quantity' => $line['quantity'],
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'notes' => 'Sale '.$sale->invoice_number,
        ]);

        if ($inventory->fresh()->isLowStock()) {
            LowStockNotification::dispatch($inventory->fresh());
        }
    }

    protected function nextInvoiceNumber(): string
    {
        $date = now()->format('Ymd');

        $latest = Sale::withTrashed()
            ->where('invoice_number', 'like', "INV-{$date}-%")
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = $latest !== null ? ((int) Str::afterLast($latest, '-') + 1) : 1;

        return "INV-{$date}-".str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<int, array<string, mixed>>  $payments
     */
    protected function summarizeMethods(array $payments): ?string
    {
        $methods = collect($payments)->pluck('method')->unique()->values()->toArray();

        return $methods === [] ? null : implode('+', $methods);
    }
}
