<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Aplica el scope para filtrar por tenant_id automáticamente.
     * Solo aplica si el usuario está autenticado y tiene tenant_id.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // El superadmin no está limitado a un tenant
            if ($user->rol === 'superadmin') {
                return;
            }

            // Si el usuario no tiene tenant asignado, no debe ver ningún registro
            // (evita que vea datos de otros tenants)
            if (!$user->tenant_id) {
                $builder->whereRaw('1 = 0');
                return;
            }

            $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
        }
    }
}