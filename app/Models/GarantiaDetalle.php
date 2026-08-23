<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class GarantiaDetalle extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'garantia_detalles';

    protected $fillable = [
        'garantia_id', 'producto_id', 'detalle_venta_id',
        'cantidad', 'condicion', 'tenant_id',
    ];

    public function garantia()
    {
        return $this->belongsTo(Garantia::class);
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