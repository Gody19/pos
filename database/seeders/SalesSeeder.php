<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $cashiers = User::where('role_name', 'cashier')->get();
        $customers = Customer::all();
        $products = Product::with('inventory')->where('status', true)->get();

        if ($cashiers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // A couple of closed shifts per cashier for history
        foreach ($cashiers as $cashier) {
            Shift::create([
                'user_id' => $cashier->id,
                'opening_balance' => 20000,
                'closing_balance' => fake()->numberBetween(150000, 600000),
                'cash_sales' => 0,
                'card_sales' => 0,
                'mobile_sales' => 0,
                'qr_sales' => 0,
                'sales_count' => 0,
                'opened_at' => now()->subDays(fake()->numberBetween(2, 30)),
                'closed_at' => now()->subDays(fake()->numberBetween(1, 29)),
                'status' => 'closed',
            ]);
        }

        $completedShifts = Shift::where('status', 'closed')->get();

        for ($i = 0; $i < 400; $i++) {
            $cashier = $cashiers->random();
            $shift = $completedShifts->random();
            $itemCount = fake()->numberBetween(1, 6);
            $selected = $products->random($itemCount);

            $subtotal = 0;
            $tax = 0;
            $lines = [];

            foreach ($selected as $product) {
                $qty = fake()->numberBetween(1, 4);
                $lineNet = $qty * (float) $product->selling_price;
                $lineTax = $lineNet * ((float) $product->tax_rate / 100);
                $subtotal += $lineNet;
                $tax += $lineTax;
                $lines[] = [$product, $qty, $lineTax];
            }

            $discount = fake()->randomElement([0, 0, 0, round($subtotal * 0.05, 2)]);
            $total = round($subtotal - $discount + $tax, 2);
            $method = fake()->randomElement(['cash', 'cash', 'cash', 'card', 'mobile', 'qr']);
            $soldAt = fake()->dateTimeBetween('-3 months', 'now');

            $sale = Sale::create([
                'invoice_number' => 'INV-DEMO-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'customer_id' => $customers->isEmpty() ? null : $customers->random()->id,
                'user_id' => $cashier->id,
                'shift_id' => $shift->id,
                'subtotal' => round($subtotal, 2),
                'discount' => $discount,
                'tax' => round($tax, 2),
                'total' => $total,
                'amount_paid' => $total,
                'change_due' => 0,
                'payment_status' => 'completed',
                'payment_method' => $method,
                'sold_at' => $soldAt,
                'created_at' => $soldAt,
            ]);

            foreach ($lines as [$product, $qty, $lineTax]) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => (float) $product->selling_price,
                    'discount' => 0,
                    'tax_rate' => (float) $product->tax_rate,
                    'tax_amount' => round($lineTax, 2),
                    'total' => round($qty * (float) $product->selling_price + $lineTax, 2),
                ]);
            }

            Payment::create([
                'sale_id' => $sale->id,
                'method' => $method,
                'amount' => $total,
                'status' => 'completed',
                'paid_at' => $soldAt,
            ]);
        }
    }
}
