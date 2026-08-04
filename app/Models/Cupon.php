<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\TenantScope;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'cupones';

    protected $fillable = [
        'tenant_id', 'reparacion_id', 'codigo', 'tipo', 'valor',
        'descripcion', 'fecha_expiracion', 'fecha_uso', 'venta_id',
        'estado', 'compartible',
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'fecha_uso' => 'datetime',
        'valor' => 'decimal:2',
        'compartible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($cupon) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $cupon->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo')
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')
                  ->orWhere('fecha_expiracion', '>', now());
            });
    }

    public function esValido(): bool
    {
        if ($this->estado !== 'activo') return false;
        if ($this->fecha_expiracion && $this->fecha_expiracion->isPast()) return false;
        return true;
    }

    public function marcarUsado(int $ventaId): void
    {
        $this->update([
            'estado' => 'usado',
            'fecha_uso' => now(),
            'venta_id' => $ventaId,
        ]);
    }

    public static function generarCodigo(): string
    {
        return 'CUP-' . strtoupper(substr(uniqid(), -6)) . '-' . rand(100, 999);
    }
}