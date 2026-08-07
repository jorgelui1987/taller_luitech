<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo', 'codigo', 'codigo_barras', 'nombre', 'descripcion', 'garantia_dias',
        'categoria_id', 'marca_id', 'proveedor_id',
        'modelo', 'color', 'almacenamiento', 'ram', 'precio_compra',
        'precio_venta', 'stock', 'stock_minimo', 'imagen', 'imei',
        'condicion', 'activo', 'tenant_id',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta'  => 'decimal:2',
        'activo'        => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($producto) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $producto->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function movimientosStock()
    {
        return $this->hasMany(MovimientoStock::class);
    }

    public function tieneStockBajo(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    public function getMargenAttribute(): float
    {
        if ($this->precio_compra > 0) {
            return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
        }
        return 0;
    }
}