<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToTenant;

class Cupon extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'cupones';

    protected $fillable = [
        'tenant_id', 'reparacion_id', 'codigo', 'tipo', 'valor',
        'descripcion', 'fecha_expiracion', 'fecha_uso', 'venta_id',
        'reparacion_uso_id', 'estado', 'compartible',
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'fecha_uso' => 'datetime',
        'valor' => 'decimal:2',
        'compartible' => 'boolean',
    ];

    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function reparacionUso()
    {
        return $this->belongsTo(Reparacion::class, 'reparacion_uso_id');
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
        if ($this->estado !== 'activo') {
            return false;
        }
        if ($this->fecha_expiracion && $this->fecha_expiracion->isPast()) {
            return false;
        }
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

    public function marcarUsadoEnReparacion(int $reparacionId): void
    {
        $this->update([
            'estado' => 'usado',
            'fecha_uso' => now(),
            'reparacion_uso_id' => $reparacionId,
        ]);
    }

    public static function generarCodigo(): string
    {
        return 'CUP-' . strtoupper(bin2hex(random_bytes(3))) . '-' . random_int(100, 999);
    }
}