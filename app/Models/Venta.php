<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_venta', 'cliente_id', 'user_id', 'fecha_venta',
        'subtotal', 'descuento', 'impuesto', 'total',
        'comision_monto', 'comision_pagada',
        'metodo_pago', 'estado', 'notas', 'tenant_id',
    ];

    protected $casts = [
        'fecha_venta'    => 'datetime',
        'subtotal'       => 'decimal:2',
        'descuento'      => 'decimal:2',
        'impuesto'       => 'decimal:2',
        'total'          => 'decimal:2',
        'comision_monto' => 'decimal:2',
        'comision_pagada'=> 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($venta) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $venta->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function cupones()
    {
        return $this->hasMany(Cupon::class);
    }

    public static function generarNumero(): string
    {
        $tenantId = auth()->check() ? auth()->user()->tenant_id : null;
        if (!$tenantId) {
            $tenant = \App\Models\Tenant::first();
            $tenantId = $tenant ? $tenant->id : 1;
        }
        
        // Buscar el número más alto existente (sin importar tenant_id)
        // Esto evita el problema de que el último registro tenga otro tenant
        $maxNumero = \Illuminate\Support\Facades\DB::table('ventas')
            ->where('numero_venta', 'like', 'VTA-%')
            ->max('numero_venta');
            
        $numero = 1;
        if ($maxNumero) {
            $parte = substr($maxNumero, 4);
            if (is_numeric($parte)) {
                $numero = (int)$parte + 1;
            }
        }
        
        // Garantizar unicidad absoluta (por si hay huecos o colisiones)
        $nuevo = 'VTA-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        $contador = 0;
        while (\Illuminate\Support\Facades\DB::table('ventas')
            ->where('numero_venta', $nuevo)->exists() && $contador < 1000) {
            $numero++;
            $nuevo = 'VTA-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
            $contador++;
        }
        
        return $nuevo;
    }
}