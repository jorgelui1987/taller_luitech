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
        'name', 'email', 'password', 'rol', 'telefono', 'foto', 'activo', 'tenant_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
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

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esVendedor(): bool
    {
        return $this->rol === 'vendedor';
    }

    public function esTecnico(): bool
    {
        return $this->rol === 'tecnico';
    }

    public function esSuperAdmin(): bool
    {
        return $this->rol === 'superadmin';
    }
}