<?php

namespace App\Policies;

use App\Models\User;

class SolicitudesTipoPolicy
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
        return $user->isRole('Administrador');
    }

    public function create(User $user)
    {
        //
        return $user->isRole('Administrador');

    }
    public function update(User $user)
    {
        //
        return $user->isRole('Administrador');

    }
}
