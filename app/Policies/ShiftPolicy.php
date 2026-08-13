<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role_name === 'admin' ? true : null;
    }

    public function open(User $user): bool
    {
        return in_array($user->role_name, ['admin', 'manager', 'cashier']);
    }

    public function close(User $user, ?Shift $shift = null): bool
    {
        if ($user->role_name === 'cashier' && $shift !== null) {
            return $shift->user_id === $user->id;
        }

        return in_array($user->role_name, ['admin', 'manager', 'cashier']);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }
}
