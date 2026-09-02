<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'rol', 'roles', 'telefono', 'foto', 'activo', 'tenant_id', 'comision_porcentaje',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
        'roles' => 'array',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'user_id');
    }

    public function reparaciones()
    {
        return $this->hasMany(Reparacion::class, 'tecnico_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Roles efectivos del usuario: la columna roles (multi-rol) más el rol
     * único antiguo como compatibilidad (usuarios creados antes de migrar).
     */
    public function rolesEfectivos(): array
    {
        $roles = $this->roles ?? [];

        if (!empty($this->rol) && !in_array($this->rol, $roles)) {
            $roles[] = $this->rol;
        }

        return array_values(array_unique(array_filter($roles)));
    }

    public function tieneRol(string $rol): bool
    {
        return in_array($rol, $this->rolesEfectivos());
    }

    public function esAdmin(): bool
    {
        return $this->tieneRol('admin');
    }

    public function esVendedor(): bool
    {
        return $this->tieneRol('vendedor');
    }

    public function esTecnico(): bool
    {
        return $this->tieneRol('tecnico');
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === 'superadmin';
    }

    // ─── Capacidades (lo que el sistema debe preguntar, no el rol) ────────
    public function puedeVender(): bool
    {
        return $this->esAdmin() || $this->esVendedor();
    }

    public function puedeReparar(): bool
    {
        return $this->esAdmin() || $this->esTecnico();
    }

    public function puedeEliminar(): bool
    {
        return $this->esAdmin() || $this->esSuperAdmin();
    }
}
