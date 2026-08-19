<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class CierreCaja extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'cierres_caja';

    protected $fillable = [
        'user_id', 'tenant_id',
        'monto_inicial', 'fecha_apertura', 'fecha_cierre',
        'ventas_efectivo', 'ventas_tarjeta', 'ventas_transferencia', 'ventas_otros',
        'total_ingresos', 'total_egresos',
        'total_esperado', 'total_contado', 'diferencia',
        'estado', 'observaciones',
    ];

    protected $casts = [
        'monto_inicial'     => 'decimal:2',
        'ventas_efectivo'   => 'decimal:2',
        'ventas_tarjeta'    => 'decimal:2',
        'ventas_transferencia' => 'decimal:2',
        'ventas_otros'      => 'decimal:2',
        'total_ingresos'    => 'decimal:2',
        'total_egresos'     => 'decimal:2',
        'total_esperado'    => 'decimal:2',
        'total_contado'     => 'decimal:2',
        'diferencia'        => 'decimal:2',
        'fecha_apertura'    => 'datetime',
        'fecha_cierre'      => 'datetime',
    ];

    /**
     * Usuario que abrió/cerró la caja.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Caja abierta actualmente para el tenant.
     */
    public static function cajaAbierta(): ?self
    {
        $tenantId = auth()->user()->tenant_id;
        return self::where('tenant_id', $tenantId)
            ->where('estado', 'abierta')
            ->latest('fecha_apertura')
            ->first();
    }

    /**
     * Hay una caja abierta?
     */
    public static function hayCajaAbierta(): bool
    {
        return self::cajaAbierta() !== null;
    }

    /**
     * Ventas del día (desde la apertura de la caja actual o desde hoy).
     */
    public static function ventasDelDia(?self $caja = null): array
    {
        $tenantId = auth()->user()->tenant_id;
        $hoy = now()->startOfDay();
        $fin = now()->endOfDay();

        // Si hay caja abierta, contar desde su fecha de apertura
        if ($caja && $caja->fecha_apertura) {
            $hoy = $caja->fecha_apertura;
        }

        $ventas = Venta::where('estado', 'completada')
            ->where('tenant_id', $tenantId)
            ->whereBetween('fecha_venta', [$hoy, $fin])
            ->get();

        $reparaciones = Reparacion::where('estado', 'entregado')
            ->where('tenant_id', $tenantId)
            ->whereBetween('fecha_entrega', [$hoy, $fin])
            ->get();

        $efectivo    = $ventas->where('metodo_pago', 'efectivo')->sum('total')
                      + $reparaciones->where('metodo_pago', 'efectivo')->sum('total');
        $tarjeta     = $ventas->whereIn('metodo_pago', ['tarjeta', 'debito', 'credito', 'cuotas'])->sum('total')
                      + $reparaciones->whereIn('metodo_pago', ['tarjeta', 'debito', 'credito', 'cuotas'])->sum('total');
        $transferencia = $ventas->where('metodo_pago', 'transferencia')->sum('total')
                      + $reparaciones->where('metodo_pago', 'transferencia')->sum('total');
        $otros = $ventas->whereNotIn('metodo_pago', ['efectivo', 'tarjeta', 'debito', 'credito', 'cuotas', 'transferencia'])->sum('total')
               + $reparaciones->whereNotIn('metodo_pago', ['efectivo', 'tarjeta', 'debito', 'credito', 'cuotas', 'transferencia'])->sum('total');

        $totalIngresos = $ventas->sum('total') + $reparaciones->sum('total');

        // Devoluciones en efectivo del día
        $egresos = \App\Models\Devolucion::whereDate('created_at', now()->toDateString())
            ->where('tenant_id', $tenantId)
            ->where('estado', 'completada')
            ->sum('total');

        return [
            'efectivo'      => $efectivo,
            'tarjeta'       => $tarjeta,
            'transferencia' => $transferencia,
            'otros'         => $otros,
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $egresos,
            'num_ventas'    => $ventas->count(),
            'num_reparaciones' => $reparaciones->count(),
        ];
    }
}