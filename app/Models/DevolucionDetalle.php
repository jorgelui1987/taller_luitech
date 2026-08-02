<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class DevolucionDetalle extends Model
{
    use HasFactory;

    protected $table = 'devolucion_detalles';

    protected $fillable = [
        'devolucion_id',
        'producto_id',
        'detalle_venta_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'subtotal',
        'condicion',
        'tenant_id',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento'       => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($detalle) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $detalle->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id');
    }
}