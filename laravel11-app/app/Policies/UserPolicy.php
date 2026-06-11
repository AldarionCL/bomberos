<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        return $user->isRole('Administrador') || $user->isCargo(['Director', 'Capitan','Capitán','Teniente 1','Teniente 2','Teniente 3','Ayudante']);
    }

    public function create(User $user)
    {
        //
        return $user->isRole('Administrador');

    }
    public function update(User $user, User $record): bool
    {
        return $user->isRole('Administrador')
            || $user->isCargo(['Director', 'Capitan', 'Capitán', 'Teniente 1', 'Teniente 2', 'Teniente 3', 'Ayudante'])
            || $user->id === $record->id;
    }
}
