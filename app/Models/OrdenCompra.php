<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class OrdenCompra extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'numero_orden', 'proveedor_id', 'user_id', 'fecha_orden', 'fecha_estimada',
        'fecha_recibida', 'subtotal', 'impuesto', 'descuento', 'total',
        'estado', 'notas', 'tenant_id',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_estimada' => 'date',
        'fecha_recibida' => 'date',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // El trait BelongsToTenant registra el scope y asigna tenant_id automáticamente
        static::creating(function ($model) {
            if ($model->tenant_id && !$model->numero_orden) {
                $model->numero_orden = static::generarNumero();
            }
        });
    }

    public static function generarNumero(): string
    {
        $prefix = 'OC-';
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $castType = ($driver === 'pgsql' || $driver === 'sqlite') ? 'INTEGER' : 'UNSIGNED';

        $maxNumero = \Illuminate\Support\Facades\DB::table('ordenes_compra')
            ->whereNotNull('numero_orden')
            ->orderByRaw("CAST(SUBSTRING(numero_orden, 4) AS {$castType}) DESC")
            ->value('numero_orden');

        $numero = 1;
        if ($maxNumero) {
            $numExtraido = (int) preg_replace('/\D/', '', $maxNumero);
            if ($numExtraido > 0) {
                $numero = $numExtraido + 1;
            }
        }

        $nuevo = $prefix . str_pad($numero, 6, '0', STR_PAD_LEFT);
        $contador = 0;
        while (\Illuminate\Support\Facades\DB::table('ordenes_compra')->where('numero_orden', $nuevo)->exists() && $contador < 1000) {
            $numero++;
            $nuevo = $prefix . str_pad($numero, 6, '0', STR_PAD_LEFT);
            $contador++;
        }

        return $nuevo;
    }

    public function proveedor() { return $this->belongsTo(Proveedor::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function detalles() { return $this->hasMany(DetalleOrdenCompra::class); }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'pendiente' => '#f59e0b',
            'aprobada' => '#06b6d4',
            'enviada' => '#7c3aed',
            'recibida_parcial' => '#f97316',
            'completada' => '#10b981',
            'cancelada' => '#dc2626',
            default => '#6b7280',
        };
    }

    public function getEstadoBgAttribute(): string
    {
        return match($this->estado) {
            'pendiente' => '#fef3c7',
            'aprobada' => '#e0f2fe',
            'enviada' => '#ede9fe',
            'recibida_parcial' => '#fff7ed',
            'completada' => '#d1fae5',
            'cancelada' => '#fee2e2',
            default => '#f3f4f6',
        };
    }
}
