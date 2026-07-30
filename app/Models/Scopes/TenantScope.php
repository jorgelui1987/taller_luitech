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
            $tenantId = Auth::user()->tenant_id;

            if ($tenantId) {
                $builder->where($model->getTable() . '.tenant_id', $tenantId);
            }
        }
    }
}