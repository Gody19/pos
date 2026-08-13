<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
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

    public function update(User $user, Customer $customer): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'cashier']);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return in_array($user->role_name, ['admin', 'manager']);
    }
}
