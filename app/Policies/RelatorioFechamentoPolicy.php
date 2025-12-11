<?php

namespace App\Policies;

use App\Models\RelatorioFechamento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RelatorioFechamentoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->papel === 'admin' || $user->papel === 'financeiro';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RelatorioFechamento $relatorioFechamento): bool
    {
        return $user->papel === 'admin' || $user->papel === 'financeiro';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->papel === 'admin' || $user->papel === 'financeiro';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RelatorioFechamento $relatorioFechamento): bool
    {
        return $user->papel === 'admin' || $user->papel === 'financeiro';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RelatorioFechamento $relatorioFechamento): bool
    {
        return $user->papel === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RelatorioFechamento $relatorioFechamento): bool
    {
        return $user->papel === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RelatorioFechamento $relatorioFechamento): bool
    {
        return $user->papel === 'admin';
    }

    /**
     * Determine whether the user can approve the model.
     * Only ADMIN can approve fechamentos for sending.
     */
    public function aprovar(User $user, RelatorioFechamento $relatorioFechamento): bool
    {
        return $user->papel === 'admin';
    }

    /**
     * Determine whether the user can reject the model.
     * Only ADMIN can reject fechamentos.
     */
    public function rejeitar(User $user, RelatorioFechamento $relatorioFechamento): bool
    {
        return $user->papel === 'admin';
    }
}
