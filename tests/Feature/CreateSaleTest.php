<?php

namespace Tests\Feature;

class CreateSaleTest extends ApiTestCase
{
    public function test_cashier_creates_completed_sale(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct(stock: 15);

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
            'payments' => [['method' => 'cash', 'amount' => round($product->selling_price * 3, 2)]],
        ], $headers);

        $response->assertCreated()
            ->assertJsonPath('data.payment_status', 'completed')
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonStructure(['data' => ['id', 'invoice_number', 'receipt']]);

        $this->assertDatabaseHas('sales', ['id' => $response->json('data.id'), 'payment_status' => 'completed']);
        $this->assertDatabaseHas('payments', ['sale_id' => $response->json('data.id'), 'method' => 'cash']);
    }

    public function test_sale_requires_open_shift(): void
    {
        $user = $this->createUser('cashier');
        $product = $this->makeProduct();

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [['method' => 'cash', 'amount' => $product->selling_price]],
        ], $this->authHeaders($user));

        $response->assertStatus(409);
    }

    public function test_payment_mismatch_is_rejected(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct();

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payments' => [['method' => 'cash', 'amount' => 5]],
        ], $headers);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('sales', ['payment_status' => 'completed']);
    }

    public function test_sale_can_be_cancelled_and_stock_restored(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct(stock: 10);

        $sale = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
            'payments' => [['method' => 'card', 'amount' => round($product->selling_price * 4, 2)]],
        ], $headers)->json('data');

        $response = $this->postJson($this->apiBase().'/sales/'.$sale['id'].'/cancel', [
            'reason' => 'Customer returned items',
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('data.payment_status', 'cancelled');

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 10]);
    }

    public function test_low_stock_event_is_logged_after_sale(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct(stock: 3);
        $product->inventory->update(['reorder_level' => 3]);

        $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [['method' => 'cash', 'amount' => $product->selling_price]],
        ], $headers)->assertCreated();

        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'quantity' => 2]);
    }
}
