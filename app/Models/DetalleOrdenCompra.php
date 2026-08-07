<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class DetalleOrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_ordenes_compra';

    protected $fillable = [
        'orden_compra_id', 'producto_id', 'cantidad_ordenada',
        'cantidad_recibida', 'precio_unitario', 'descuento', 'subtotal', 'tenant_id',
    ];

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
    public function ordenCompra() { return $this->belongsTo(OrdenCompra::class); }
    public function producto() { return $this->belongsTo(Producto::class); }

    public function getPendienteRecibirAttribute(): int
    {
        return $this->cantidad_ordenada - $this->cantidad_recibida;
    }
}