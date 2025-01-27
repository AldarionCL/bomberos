<?php

namespace App\Policies;

use App\Models\User;

class UserRolePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        //
        return false;
    }

    public function create(User $user)
    {
        //
        return $user->isRole('Administrador') || $user->isCargo('Director');

    }
    public function update(User $user)
    {
        //
        return $user->isRole('Administrador');

    }
}
