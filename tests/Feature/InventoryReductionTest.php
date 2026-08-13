<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\Sale;

class InventoryReductionTest extends ApiTestCase
{
    public function test_stock_is_reduced_after_sale(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct(stock: 20);

        $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 6]],
            'payments' => [['method' => 'cash', 'amount' => round($product->selling_price * 6, 2)]],
        ], $headers)->assertCreated();

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 14]);
    }

    public function test_inventory_movement_is_recorded(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct(stock: 20);

        $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payments' => [['method' => 'mobile', 'amount' => round($product->selling_price * 2, 2)]],
        ], $headers)->assertCreated();

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => InventoryMovement::TYPE_SALE,
            'quantity' => 2,
            'reference_type' => Sale::class,
        ]);
    }

    public function test_out_of_stock_product_is_rejected(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct(stock: 0);

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [['method' => 'cash', 'amount' => $product->selling_price]],
        ], $headers);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('sales', ['payment_status' => 'completed']);
    }

    public function test_inventory_clerk_can_adjust_stock(): void
    {
        $user = $this->createUser('inventory_clerk');
        $headers = $this->authHeaders($user);

        $product = $this->makeProduct(stock: 10);

        $response = $this->postJson($this->apiBase().'/inventory/adjust', [
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => 'in',
            'reason' => 'Delivery restock',
        ], $headers);

        $response->assertOk();

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 15]);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
        ]);
    }

    public function test_cashier_cannot_adjust_stock(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $product = $this->makeProduct();

        $response = $this->postJson($this->apiBase().'/inventory/adjust', [
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => 'in',
        ], $headers);

        $response->assertStatus(403);
    }
}
