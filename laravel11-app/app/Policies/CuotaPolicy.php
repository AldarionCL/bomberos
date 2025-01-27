<?php

namespace App\Policies;

use App\Models\User;

class CuotaPolicy
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
        return $user->isRole('Administrador') || $user->isCargo('Tesorero');
    }

    public function create(User $user)
    {
        //
        return $user->isRole('Administrador') || $user->isCargo('Tesorero');

    }
    public function update(User $user)
    {
        //
        return $user->isRole('Administrador') || $user->isCargo('Tesorero');

    }
}
