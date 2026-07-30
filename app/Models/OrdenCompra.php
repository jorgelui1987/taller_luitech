<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class OrdenCompra extends Model
{
    use HasFactory;

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
        static::addGlobalScope(new TenantScope);
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
            if (!$model->numero_orden) {
                $model->numero_orden = static::generarNumero();
            }
        });
    }

    public static function generarNumero(): string
    {
        $prefix = 'OC-';
        $last = static::where('tenant_id', auth()->user()->tenant_id ?? 0)
            ->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->numero_orden, 3)) + 1 : 1;
        return $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function tenant() { return $this->belongsTo(Tenant::class); }
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