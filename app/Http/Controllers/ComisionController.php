<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reparacion;
use App\Models\Venta;
use Illuminate\Http\Request;

class ComisionController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Obtener todos los técnicos del tenant
        $tecnicos = User::where('tenant_id', $tenantId)
            ->where('rol', 'tecnico')
            ->where('activo', true)
            ->orderBy('name')
            ->get();

        // Permitir filtrar por técnico y rango de fechas
        $tecnicoId = $request->tecnico_id;
        $fechaDesde = $request->fecha_desde ?? now()->startOfMonth()->format('Y-m-d');
        $fechaHasta = $request->fecha_hasta ?? now()->format('Y-m-d');

        // Base query para reparaciones entregadas
        $query = Reparacion::with(['cliente', 'tecnico'])
            ->where('estado', 'entregado')
            ->whereBetween('fecha_entrega', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);

        if ($tecnicoId) {
            $query->where('tecnico_id', $tecnicoId);
        }

        $reparaciones = $query->orderByDesc('fecha_entrega')->get();

        // Calcular totales por técnico
        $totalesPorTecnico = $this->calcularTotalesPorTecnico($reparaciones);

        return view('comisiones.index', compact(
            'tecnicos', 'tecnicoId', 'fechaDesde', 'fechaHasta',
            'reparaciones', 'totalesPorTecnico'
        ));
    }

    private function calcularTotalesPorTecnico($reparaciones): array
    {
        $totalesPorTecnico = [];
        foreach ($reparaciones as $rep) {
            $tid = $rep->tecnico_id;
            if (!$tid) {
                continue;
            }

            $tecnicoObj = $rep->tecnico;
            $porcentaje = $this->obtenerPorcentajeComision($rep, $tecnicoObj);

            // Base de comisión SIEMPRE = Presupuesto - Costo de Repuesto
            $baseManoObra = $rep->baseComision();

            $comisionMonto = (float)($rep->comision_monto ?? 0);

            // Calcular monto si no estaba asignado o si faltaba porcentaje
            if ($porcentaje > 0 && ($comisionMonto == 0 || $rep->comision_porcentaje === null)) {
                $comisionMonto = round($baseManoObra * ($porcentaje / 100), 2);
                if (!$rep->comision_pagada) {
                    $rep->update([
                        'comision_porcentaje' => $porcentaje,
                        'comision_monto'      => $comisionMonto,
                    ]);
                }
            }

            // Si el monto recalculado es asignado temporalmente para la vista
            $rep->comision_porcentaje = $porcentaje;
            $rep->comision_monto = $comisionMonto;

            if (!isset($totalesPorTecnico[$tid])) {
                $totalesPorTecnico[$tid] = [
                    'nombre'             => $tecnicoObj->name ?? '—',
                    'total_reparado'     => 0,
                    'comision_total'     => 0,
                    'comision_pagada'    => 0,
                    'comision_pendiente' => 0,
                    'cantidad'           => 0,
                    'tecnico'            => $tecnicoObj,
                ];
            }

            $totalesPorTecnico[$tid]['total_reparado'] += $baseManoObra;
            $totalesPorTecnico[$tid]['comision_total'] += $comisionMonto;
            $totalesPorTecnico[$tid]['cantidad']++;

            if ($rep->comision_pagada) {
                $totalesPorTecnico[$tid]['comision_pagada'] += $comisionMonto;
            } else {
                $totalesPorTecnico[$tid]['comision_pendiente'] += $comisionMonto;
            }
        }

        return $totalesPorTecnico;
    }

    private function obtenerPorcentajeComision($rep, $tecnicoObj): float
    {
        $porcentaje = $rep->comision_porcentaje;

        // Si la reparación no tiene porcentaje guardado pero el técnico si tiene % en su perfil
        if (($porcentaje === null || $porcentaje == 0) && $tecnicoObj && $tecnicoObj->comision_porcentaje > 0) {
            $porcentaje = (float)$tecnicoObj->comision_porcentaje;
        }

        return (float) $porcentaje;
    }

    /**
     * Marcar una comisión como pagada y descontarla de Ventas
     */
    public function pagar(Request $request, Reparacion $reparacion)
    {
        $request->validate([
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        if (!$reparacion->comision_pagada) {
            // Asegurar que comision_monto esté calculado
            // Base SIEMPRE = Presupuesto - Costo de Repuesto
            if (!$reparacion->comision_monto || $reparacion->comision_monto <= 0) {
                $reparacion->comision_monto = $reparacion->montoComision();
            }

            $reparacion->update([
                'comision_pagada'     => true,
                'comision_fecha_pago' => now(),
            ]);

            // Restar de Venta
            if ($reparacion->comision_monto > 0) {
                $metodoPago = $request->metodo_pago ?? 'efectivo';
                Venta::create([
                    'tenant_id'    => auth()->user()?->tenant_id ?? $reparacion->tenant_id,
                    'numero_venta' => Venta::generarNumero(),
                    'cliente_id'   => $reparacion->cliente_id,
                    'user_id'      => auth()->id(),
                    'fecha_venta'  => now(),
                    'subtotal'     => -$reparacion->comision_monto,
                    'impuesto'     => 0,
                    'descuento'    => 0,
                    'total'        => -$reparacion->comision_monto,
                    'metodo_pago'  => $metodoPago,
                    'estado'       => 'completada',
                    'notas'        => "Pago de comisión a técnico ({$reparacion->tecnico?->name}) - Orden {$reparacion->numero_orden}",
                ]);
            }
        }

        return back()->with('success', "Comisión de {$reparacion->numero_orden} (S/ " . number_format($reparacion->comision_monto, 2) . ") marcada como pagada y restada de ventas.");
    }

    /**
     * Marcar todas las comisiones de un técnico como pagadas y descontarlas de Ventas
     */
    public function pagarTodo(Request $request, User $tecnico)
    {
        $request->validate([
            'metodo_pago' => 'nullable|string|max:50',
        ]);

        $reparaciones = Reparacion::where('tecnico_id', $tecnico->id)
            ->where('estado', 'entregado')
            ->where('comision_pagada', false)
            ->get();

        $metodoPago = $request->metodo_pago ?? 'efectivo';
        $montoTotalComisiones = 0;

        foreach ($reparaciones as $rep) {
            $comisionMonto = (float)$rep->comision_monto;
            if ($comisionMonto <= 0) {
                // Base SIEMPRE = Presupuesto - Costo de Repuesto
                $comisionMonto = $rep->montoComision();
                $rep->comision_monto = $comisionMonto;
            }

            if ($comisionMonto > 0) {
                $rep->update([
                    'comision_pagada'     => true,
                    'comision_fecha_pago' => now(),
                    'comision_monto'      => $comisionMonto,
                ]);
                $montoTotalComisiones += $comisionMonto;
            } else {
                $rep->update([
                    'comision_pagada'     => true,
                    'comision_fecha_pago' => now(),
                ]);
            }
        }

        if ($montoTotalComisiones > 0) {
            Venta::create([
                'tenant_id'    => auth()->user()?->tenant_id ?? $tecnico->tenant_id,
                'numero_venta' => Venta::generarNumero(),
                'cliente_id'   => null,
                'user_id'      => auth()->id(),
                'fecha_venta'  => now(),
                'subtotal'     => -$montoTotalComisiones,
                'impuesto'     => 0,
                'descuento'    => 0,
                'total'        => -$montoTotalComisiones,
                'metodo_pago'  => $metodoPago,
                'estado'       => 'completada',
                'notas'        => "Pago total de comisiones a técnico {$tecnico->name} (S/ " . number_format($montoTotalComisiones, 2) . ")",
            ]);
        }

        return back()->with('success', "Todas las comisiones de {$tecnico->name} (Total: S/ " . number_format($montoTotalComisiones, 2) . ") fueron marcadas como pagadas y restadas de ventas.");
    }
}
