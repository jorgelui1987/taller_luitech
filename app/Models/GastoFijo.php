<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class GastoFijo extends Model
{
    use HasFactory;

    protected $table = 'gastos_fijos';

    protected $fillable = [
        'nombre',
        'monto',
        'categoria',
        'descripcion',
        'fecha',
        'activo',
        'tenant_id',
    ];

    protected $casts = [
        'monto'  => 'decimal:2',
        'fecha'  => 'date',
        'activo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($gasto) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $gasto->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
