<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class DetalleVenta extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'venta_id', 'producto_id', 'cantidad', 'precio_unitario',
        'descuento', 'subtotal', 'imei_vendido', 'tenant_id',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento'       => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
