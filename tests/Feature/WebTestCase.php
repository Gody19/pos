<?php

namespace Tests\Feature;

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class WebTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    protected function createUser(string $role = 'cashier', array $overrides = []): \App\Models\User
    {
        $user = \App\Models\User::factory()->create([
            'role_name' => $role,
            'status' => true,
            ...$overrides,
        ]);
        $user->syncRoles([$role]);

        return $user;
    }

    protected function createProduct(int $stock = 20): \App\Models\Product
    {
        $category = \App\Models\Category::factory()->create();
        $product = \App\Models\Product::factory()->create([
            'category_id' => $category->id,
            'status' => true,
            'tax_rate' => 0,
        ]);

        \App\Models\Inventory::updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $stock, 'reorder_level' => 5]
        );

        return $product->load('inventory');
    }

    protected function openShift(\App\Models\User $user, float $amount = 10000): \App\Models\Shift
    {
        return app(\App\Services\ShiftService::class)->open($user, $amount);
    }
}