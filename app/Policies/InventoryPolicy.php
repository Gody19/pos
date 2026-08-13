<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role_name === 'admin' ? true : null;
    }

    public function adjust(User $user, ?Inventory $inventory = null): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'inventory_clerk']);
    }

    public function viewMovements(User $user): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'inventory_clerk']);
    }
}
