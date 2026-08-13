<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role_name === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'inventory_clerk']);
    }

    public function update(User $user, Product $product): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'inventory_clerk']);
    }

    public function delete(User $user, Product $product): bool
    {
        return in_array($user->role_name, ['admin', 'manager']);
    }
}
