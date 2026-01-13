<?php

namespace App\Policies;

use App\Models\Office;
use App\Models\User;

class OfficePolicy
{
    /**
     * Todos los usuarios autenticados pueden ver la lista.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Todos los usuarios autenticados pueden ver el detalle.
     */
    public function view(User $user, Office $office): bool
    {
        return true;
    }

    /**
     * Solo el admin puede crear.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Solo el admin puede editar.
     */
    public function update(User $user, Office $office): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Solo el admin puede eliminar.
     */
    public function delete(User $user, Office $office): bool
    {
        return $user->role === 'admin';
    }
}