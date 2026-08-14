<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'pos.cart';

    /**
     * @return array<string, mixed>
     */
    public function getState(): array
    {
        $cart = $this->items();

        $subtotal = round($cart->sum(fn ($item) => $item['price'] * $item['quantity']), 2);
        $tax = round($cart->sum(fn ($item) => $item['price'] * $item['quantity'] * ($item['tax_rate'] / 100)), 2);
        $discount = (float) session()->get(self::SESSION_KEY.'.discount', 0);

        if ($discount < 0 || $discount > $subtotal) {
            $discount = 0;
        }

        $total = round($subtotal - $discount + $tax, 2);

        return [
            'cart' => $cart->values()->map(fn ($item) => [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'tax_rate' => $item['tax_rate'],
                'total' => round($item['price'] * $item['quantity'], 2),
            ])->all(),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'count' => $cart->sum('quantity'),
            'customer_id' => session()->get(self::SESSION_KEY.'.customer_id'),
            'notes' => session()->get(self::SESSION_KEY.'.notes'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(): Collection
    {
        return collect(session()->get(self::SESSION_KEY.'.items', []));
    }

    public function add(int $productId, int $quantity = 1): array
    {
        $product = Product::with('inventory')->find($productId);

        if ($product === null) {
            throw new \InvalidArgumentException('Product not found.');
        }

        if (! $product->status) {
            throw new \InvalidArgumentException('This product is not available for sale.');
        }

        $quantity = max(1, $quantity);
        $items = $this->items()->keyBy('product_id');
        $current = (int) ($items->get($productId)['quantity'] ?? 0);
        $available = $product->stockQuantity();

        if ($available < $current + $quantity) {
            throw new OutOfStockException($product->name, $available);
        }

        $items->put($productId, [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->selling_price,
            'tax_rate' => (float) $product->tax_rate,
            'quantity' => $current + $quantity,
        ]);

        session()->put(self::SESSION_KEY.'.items', $items->values()->all());

        return $this->getState();
    }

    public function updateQuantity(int $productId, int $delta): array
    {
        $items = $this->items()->keyBy('product_id');

        if (! $items->has($productId)) {
            throw new \InvalidArgumentException('Item is not in the cart.');
        }

        $product = Product::with('inventory')->find($productId);
        $newQty = (int) $items->get($productId)['quantity'] + $delta;

        if ($newQty <= 0) {
            return $this->remove($productId);
        }

        $available = $product?->stockQuantity() ?? 0;

        if ($newQty > $available) {
            throw new OutOfStockException($product?->name ?? 'Product', $available);
        }

        $line = $items->get($productId);
        $line['quantity'] = $newQty;
        $items->put($productId, $line);

        session()->put(self::SESSION_KEY.'.items', $items->values()->all());

        return $this->getState();
    }

    public function remove(int $productId): array
    {
        $items = $this->items()->filter(fn ($item) => $item['product_id'] !== $productId)->values();

        session()->put(self::SESSION_KEY.'.items', $items->all());

        return $this->getState();
    }

    public function setMeta(?int $customerId = null, ?float $discount = null, ?string $notes = null): void
    {
        if ($customerId !== null) {
            session()->put(self::SESSION_KEY.'.customer_id', $customerId);
        }
        if ($discount !== null) {
            session()->put(self::SESSION_KEY.'.discount', $discount);
        }
        if ($notes !== null) {
            session()->put(self::SESSION_KEY.'.notes', $notes);
        }
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    /**
     * Build the payload expected by SaleService::create from the session cart.
     *
     * @return array<int, array<string, mixed>>
     */
    public function saleItems(): array
    {
        return $this->items()->map(fn ($item) => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'discount' => 0,
            'notes' => null,
        ])->values()->all();
    }
}