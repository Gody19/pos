<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->role_name === 'admin' ? true : null;
    }

    public function manage(User $user): bool
    {
        return $user->role_name === 'admin';
    }
}
