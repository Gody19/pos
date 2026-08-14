<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;

class SaleFlowTest extends WebTestCase
{
    public function test_cashier_adds_product_to_cart(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);
        $product = $this->createProduct(stock: 10);

        $this->actingAs($user)
            ->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('cart.0.product_id', $product->id);
    }

    public function test_cart_rejects_out_of_stock_items(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);
        $product = $this->createProduct(stock: 0);

        $this->actingAs($user)
            ->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 1])
            ->assertStatus(422);
    }

    public function test_cart_quantity_can_be_updated_and_removed(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);
        $product = $this->createProduct(stock: 10);

        $this->actingAs($user)
            ->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 1])
            ->assertOk();

        $this->actingAs($user)
            ->post('/pos/cart/update', ['product_id' => $product->id, 'quantity' => 1])
            ->assertOk()
            ->assertJsonPath('count', 2);

        $this->actingAs($user)
            ->post('/pos/cart/remove', ['product_id' => $product->id])
            ->assertOk()
            ->assertJsonPath('count', 0);
    }

    public function test_checkout_completes_sale_and_updates_inventory(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);
        $product = $this->createProduct(stock: 15);
        $total = $product->selling_price * 3;

        $this->actingAs($user)->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 3]);

        $response = $this->actingAs($user)->post('/pos/checkout', [
            'payments' => [['method' => 'cash', 'amount' => $total]],
        ]);

        $response->assertOk()->assertJsonPath('message', 'Sale completed successfully.');

        $sale = Sale::find($response->json('sale_id'));
        $this->assertNotNull($sale);
        $this->assertEquals('completed', $sale->payment_status);
        $this->assertEquals($total, (float) $sale->total);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'quantity' => 12,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('receipts', ['sale_id' => $sale->id]);
    }

    public function test_checkout_requires_payments_to_match_total(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);
        $product = $this->createProduct(stock: 10);

        $this->actingAs($user)->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/pos/checkout', [
            'payments' => [['method' => 'cash', 'amount' => 1]],
        ])->assertStatus(422);
    }

    public function test_split_payments_are_supported(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);
        $product = $this->createProduct(stock: 10);
        $total = (float) $product->selling_price;

        $this->actingAs($user)->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->post('/pos/checkout', [
            'payments' => [
                ['method' => 'cash', 'amount' => $total * 0.5],
                ['method' => 'card', 'amount' => $total * 0.5],
            ],
        ]);

        $response->assertOk();

        $sale = Sale::find($response->json('sale_id'));
        $this->assertCount(2, $sale->payments);
    }

    public function test_receipt_page_loads_after_sale(): void
    {
        $user = $this->createUser('cashier');
        $this->openShift($user);
        $product = $this->createProduct(stock: 5);

        $this->actingAs($user)->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->post('/pos/checkout', [
            'payments' => [['method' => 'cash', 'amount' => $product->selling_price]],
        ]);

        $sale = Sale::find($response->json('sale_id'));

        $this->actingAs($user)
            ->get(route('sales.receipt', $sale))
            ->assertOk()
            ->assertSee($sale->invoice_number)
            ->assertSee('Sale completed successfully');
    }

    public function test_sale_cannot_be_created_without_open_shift(): void
    {
        $user = $this->createUser('cashier');
        $product = $this->createProduct(stock: 5);

        $this->actingAs($user)->post('/pos/cart/add', ['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/pos/checkout', [
            'payments' => [['method' => 'cash', 'amount' => $product->selling_price]],
        ])->assertStatus(422);
    }
}