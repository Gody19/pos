<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
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
        return in_array($user->role_name, ['admin', 'manager', 'cashier']);
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->role_name === 'cashier'
            ? $sale->user_id === $user->id
            : in_array($user->role_name, ['admin', 'manager']);
    }

    public function cancel(User $user, Sale $sale): bool
    {
        return in_array($user->role_name, ['admin', 'manager'])
            || ($user->role_name === 'cashier' && $sale->user_id === $user->id);
    }
}
