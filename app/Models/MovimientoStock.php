<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimientos_stock';

    protected $fillable = [
        'producto_id',
        'tipo',
        'motivo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'observacion',
        'user_id',
        'venta_id',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($movimiento) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $movimiento->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function esEntrada(): bool
    {
        return $this->tipo === 'entrada';
    }

    public function esSalida(): bool
    {
        return $this->tipo === 'salida';
    }

    public function esAjuste(): bool
    {
        return $this->tipo === 'ajuste';
    }

    public function getCantidadAbsolutaAttribute(): int
    {
        return abs($this->cantidad);
    }

    public function getMotivoLabelAttribute(): string
    {
        $labels = [
            'venta'      => 'Venta',
            'compra'     => 'Compra / Reposición',
            'ajuste'     => 'Ajuste manual',
            'devolucion' => 'Devolución',
            'perdida'    => 'Pérdida',
            'daño'       => 'Dañado / Defectuoso',
            'sobrante'   => 'Sobrante en inventario',
            'cancelacion'=> 'Cancelación de venta',
        ];
        return $labels[$this->motivo] ?? ucfirst($this->motivo);
    }
}