<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class DevolucionDetalle extends Model
{
    use HasFactory, BelongsToTenant;

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