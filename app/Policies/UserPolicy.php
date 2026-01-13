<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determina si el usuario puede ver la lista de usuarios.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determina si el usuario puede ver un usuario específico.
     */
    public function view(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determina si el usuario puede crear usuarios.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determina si el usuario puede actualizar/editar usuarios.
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determina si el usuario puede eliminar usuarios.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }
}