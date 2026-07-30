<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'empresa',
        'subdominio',
        'dominio',
        'email_contacto',
        'telefono_contacto',
        'plan',
        'estado',
        'logo',
        'pais',
        'moneda',
        'simbolo_moneda',
        'impuesto',
        'max_usuarios',
        'max_productos',
        'fecha_expiracion',
        'configuracion_extra',
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'configuracion_extra' => 'array',
        'max_usuarios' => 'integer',
        'max_productos' => 'integer',
        'impuesto' => 'decimal:2',
    ];

    // ─── Relaciones ────────────────────────────────────────────────
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function configuracion(): HasMany
    {
        return $this->hasMany(Configuracion::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function reparaciones(): HasMany
    {
        return $this->hasMany(Reparacion::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // ─── Helpers ───────────────────────────────────────────────────
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function estaSuspendido(): bool
    {
        return $this->estado === 'suspendido';
    }

    public function haExpirado(): bool
    {
        return $this->fecha_expiracion && $this->fecha_expiracion->isPast();
    }

    public function puedeAgregarUsuario(): bool
    {
        return $this->usuarios()->count() < $this->max_usuarios;
    }

    public function puedeAgregarProducto(): bool
    {
        return $this->productos()->count() < $this->max_productos;
    }

    /**
     * Obtiene el tenant actual basado en el subdominio/dominio.
     */
    public static function current(): ?self
    {
        $host = request()->getHost();
        $subdominio = explode('.', $host)[0] ?? null;

        if (!$subdominio || $subdominio === 'www' || $subdominio === 'localhost' || $subdominio === '127') {
            // Es el panel principal / superadmin
            return null;
        }

        return static::where('subdominio', $subdominio)
            ->orWhere('dominio', $host)
            ->first();
    }

    /**
     * Obtiene el tenant_id actual (para usar en scopes).
     */
    public static function currentId(): ?int
    {
        $tenant = static::current();
        return $tenant?->id;
    }
}