<?php

namespace App\Policies;

use App\Models\User;

class CuotasPersonasPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function view(User $user)
    {
        //
        return $user->isRole('Administrador') || $user->isCargo('Tesorero') || $record->idUsuario == $user->id;
    }

    public function viewAny(User $user, $record)
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
//        return $user->isRole('Administrador') || $user->isCargo('Tesorero');
        return true;

    }
}
