<?php

namespace App\Policies;

use App\Models\User;

class DocumentosTipoPolicy
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
    public function delete(User $user)
    {
        //
        return $user->isRole('Administrador');

    }
}
