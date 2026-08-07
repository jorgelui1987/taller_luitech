<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre', 'contacto', 'telefono', 'email', 'direccion',
        'ruc', 'notas', 'activo', 'tenant_id',
    ];

    protected $casts = ['activo' => 'boolean'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function ordenesCompra() { return $this->hasMany(OrdenCompra::class); }
}