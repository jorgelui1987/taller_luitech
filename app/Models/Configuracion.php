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
        'zona_horaria',
        'terminos_garantia',
        'instagram',
        'facebook',
        'tiktok',
        'horario_atencion',
        'descripcion_corta',
        'pagina_publica_activa',
        'cupon_automatico_al_entregar',
        'cupon_descuento_porcentaje',
        'cupon_dias_validez',
        'tenant_id',
    ];

    protected $casts = [
        'pagina_publica_activa' => 'boolean',
        'cupon_automatico_al_entregar' => 'boolean',
        'cupon_descuento_porcentaje' => 'decimal:2',
        'cupon_dias_validez' => 'integer',
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