<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class ApiTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    protected function seedRoles(): void
    {
        foreach (['admin', 'manager', 'cashier', 'inventory_clerk'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    protected function createUser(string $role = 'cashier'): User
    {
        $user = User::factory()->create([
            'role_name' => $role,
            'status' => true,
        ]);
        $user->syncRoles([$role]);

        return $user;
    }

    protected function actingAsUser(User $user): self
    {
        return $this->actingAs($user, 'sanctum');
    }

    protected function authHeaders(User $user): array
    {
        $token = $user->createToken('test')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token, 'Accept' => 'application/json'];
    }

    protected function makeProduct(int $stock = 20, ?Category $category = null): Product
    {
        $product = Product::factory()->create([
            'category_id' => $category?->id ?? Category::factory()->create()->id,
            'status' => true,
            'tax_rate' => 0,
        ]);

        Inventory::updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $stock, 'reorder_level' => 5]
        );

        return $product->load('inventory');
    }

    protected function apiBase(): string
    {
        return '/api/v1';
    }
}
