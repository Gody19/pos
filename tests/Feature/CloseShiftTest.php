<?php

namespace Tests\Feature;

class CloseShiftTest extends ApiTestCase
{
    public function test_cashier_can_close_open_shift(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 20000], $headers)->assertCreated();

        $response = $this->postJson($this->apiBase().'/shifts/close', [
            'closing_balance' => 120000,
            'notes' => 'End of day',
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('data.status', 'closed')
            ->assertJsonPath('data.closing_balance', 120000);

        $this->assertDatabaseHas('shifts', [
            'user_id' => $user->id,
            'status' => 'closed',
        ]);
    }

    public function test_cannot_close_when_no_shift_is_open(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $response = $this->postJson($this->apiBase().'/shifts/close', [
            'closing_balance' => 50000,
        ], $headers);

        $response->assertStatus(409);
    }

    public function test_shift_totals_reflect_sales_made_during_shift(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct(stock: 10);
        $total = round($product->selling_price * 2, 2);

        $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payments' => [['method' => 'cash', 'amount' => $total]],
        ], $headers)->assertCreated();

        $response = $this->postJson($this->apiBase().'/shifts/close', [
            'closing_balance' => $total + 10000,
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('data.sales_count', 1);

        $this->assertEquals($total, $response->json('data.cash_sales'));

        $this->assertDatabaseHas('shifts', [
            'user_id' => $user->id,
            'cash_sales' => $total,
            'sales_count' => 1,
        ]);
    }
}
