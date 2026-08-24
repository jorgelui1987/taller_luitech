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
        'pais',
        'rut_emisor',
        'razon_social',
        'giro',
        'comuna_ciudad',
        'proveedor_dte',
        'dte_certificado',
        'certificado_password',
        'facturacion_electronica_activa',
        'mercadopago_activo',
        'mercadopago_public_key',
        'mercadopago_access_token',
        'mercadopago_device_id',
        'mercadopago_webhook_secret',
        'zona_horaria',
        'terminos_garantia',
        'instagram',
        'facebook',
        'tiktok',
        'horario_atencion',
        'mapa_url',
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
        'facturacion_electronica_activa' => 'boolean',
        'mercadopago_activo' => 'boolean',
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
     * Si no hay usuario autenticado (ej. rutas públicas como /manifest.json),
     * resuelve el tenant por subdominio/dominio para no devolver siempre la
     * configuración de la primera empresa registrada.
     */
    public static function empresa(): ?self
    {
        // 1. Usuario autenticado → filtrar por su tenant
        if (auth()->check() && auth()->user()->tenant_id) {
            return static::where('tenant_id', auth()->user()->tenant_id)->first();
        }

        // 2. Tenant ya inyectado por el middleware IdentifyTenant (request->tenant)
        $tenantRequest = request()->route('tenant') ?? request('tenant');
        if ($tenantRequest instanceof Tenant) {
            return static::where('tenant_id', $tenantRequest->id)->first();
        }

        // 3. Header X-Tenant (útil en desarrollo y para el manifest)
        if (request()->hasHeader('X-Tenant')) {
            $tenant = Tenant::where('subdominio', request()->header('X-Tenant'))->first();
            if ($tenant) {
                return static::where('tenant_id', $tenant->id)->first();
            }
        }

        // 4. Resolver el tenant por subdominio/dominio (rutas públicas sin sesión)
        $tenant = Tenant::current();
        if ($tenant) {
            return static::where('tenant_id', $tenant->id)->first();
        }

        // Fallback: sin tenant identificado → primera configuración (panel principal)
        return static::first();
    }
}
