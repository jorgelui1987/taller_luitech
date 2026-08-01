<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = [
        'nombre_tienda',
        'ruc',
        'direccion',
        'telefono',
        'whatsapp',
        'whatsapp_notificaciones',
        'email',
        'logo',
        'igv',
        'moneda',
        'simbolo_moneda',
        'terminos_garantia',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($config) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $config->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtiene la configuración de la empresa para el tenant actual.
     */
    public static function empresa(): ?self
    {
        if (auth()->check() && auth()->user()->tenant_id) {
            return static::where('tenant_id', auth()->user()->tenant_id)->first();
        }
        return static::first();
    }
}