<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Devolucion extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'devoluciones';

    protected $fillable = [
        'numero_devolucion',
        'venta_id',
        'cliente_id',
        'user_id',
        'fecha_devolucion',
        'motivo',
        'tipo',
        'estado',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'tipo_reembolso',
        'observacion',
        'tenant_id',
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
        'subtotal'         => 'decimal:2',
        'descuento'        => 'decimal:2',
        'impuesto'         => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(DevolucionDetalle::class);
    }

    public function detallesProducto()
    {
        return $this->hasMany(DevolucionDetalle::class)->with('producto');
    }

    public static function generarNumero(): string
    {
        $tenantId = auth()->check() ? auth()->user()->tenant_id : null;
        if (!$tenantId) {
            $tenant = \App\Models\Tenant::first();
            $tenantId = $tenant ? $tenant->id : 1;
        }

        $maxNumero = \Illuminate\Support\Facades\DB::table('devoluciones')
            ->where('numero_devolucion', 'like', 'DEV-%')
            ->max('numero_devolucion');

        $numero = 1;
        if ($maxNumero) {
            $parte = substr($maxNumero, 4);
            if (is_numeric($parte)) {
                $numero = (int)$parte + 1;
            }
        }

        $nuevo = 'DEV-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        $contador = 0;
        while (\Illuminate\Support\Facades\DB::table('devoluciones')
            ->where('numero_devolucion', $nuevo)->exists() && $contador < 1000) {
            $numero++;
            $nuevo = 'DEV-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
            $contador++;
        }

        return $nuevo;
    }
}