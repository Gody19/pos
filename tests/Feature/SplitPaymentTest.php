<?php

namespace Tests\Feature;

class SplitPaymentTest extends ApiTestCase
{
    public function test_split_payment_creates_multiple_payment_records(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct();
        $total = round($product->selling_price, 2);
        $first = round($total / 3, 2);
        $second = round($total - $first, 2);

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [
                ['method' => 'cash', 'amount' => $first],
                ['method' => 'card', 'amount' => $second],
            ],
        ], $headers);

        $response->assertCreated()
            ->assertJsonPath('data.payment_method', 'cash+card');

        $saleId = $response->json('data.id');

        $this->assertDatabaseHas('payments', ['sale_id' => $saleId, 'method' => 'cash', 'amount' => $first]);
        $this->assertDatabaseHas('payments', ['sale_id' => $saleId, 'method' => 'card', 'amount' => $second]);
    }

    public function test_split_payment_that_does_not_sum_to_total_is_rejected(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct();
        $total = round($product->selling_price, 2);

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [
                ['method' => 'cash', 'amount' => round($total * 0.4, 2)],
                ['method' => 'qr', 'amount' => round($total * 0.4, 2)],
            ],
        ], $headers);

        $response->assertStatus(422);
    }

    public function test_empty_payments_are_rejected(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct();

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [],
        ], $headers);

        $response->assertStatus(422);
    }

    public function test_four_way_split_payment(): void
    {
        $user = $this->createUser('cashier');
        $headers = $this->authHeaders($user);

        $this->postJson($this->apiBase().'/shifts/open', ['opening_balance' => 10000], $headers)->assertCreated();

        $product = $this->makeProduct();
        $total = round($product->selling_price, 2);
        $quarter = round($total / 4, 2);

        $response = $this->postJson($this->apiBase().'/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'payments' => [
                ['method' => 'cash', 'amount' => $quarter],
                ['method' => 'card', 'amount' => $quarter],
                ['method' => 'mobile', 'amount' => $quarter],
                ['method' => 'qr', 'amount' => round($total - 3 * $quarter, 2)],
            ],
        ], $headers);

        $response->assertCreated()
            ->assertJsonCount(4, 'data.payments');
    }
}
