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
        'metodo_pago', 'estado', 'notas', 'tenant_id',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal'    => 'decimal:2',
        'descuento'   => 'decimal:2',
        'impuesto'    => 'decimal:2',
        'total'       => 'decimal:2',
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

    public static function generarNumero(): string
    {
        $tenantId = auth()->check() ? auth()->user()->tenant_id : null;
        if (!$tenantId) {
            $tenant = \App\Models\Tenant::first();
            $tenantId = $tenant ? $tenant->id : 1;
        }
        
        // Buscar el último registro por ID descendente
        $ultimo = \Illuminate\Support\Facades\DB::table('ventas')
            ->where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first(['id', 'numero_venta']);
            
        $numero = 1;
        if ($ultimo && $ultimo->numero_venta) {
            $parte = substr($ultimo->numero_venta, 4);
            if (is_numeric($parte)) {
                $numero = (int)$parte + 1;
            }
        }
        
        $nuevo = 'VTA-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        
        // Garantizar unicidad (por si el último fue cancelado)
        $contador = 0;
        while (\Illuminate\Support\Facades\DB::table('ventas')
            ->where('tenant_id', $tenantId)
            ->where('numero_venta', $nuevo)->exists() && $contador < 100) {
            $numero++;
            $nuevo = 'VTA-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
            $contador++;
        }
        
        return $nuevo;
    }
}