<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role_name === 'admin' || $user->role_name === 'manager' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'inventory_clerk']);
    }

    public function update(User $user, Category $category): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'inventory_clerk']);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->role_name === 'admin' || $user->role_name === 'manager';
    }
}
