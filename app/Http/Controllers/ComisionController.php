<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Reparacion;
use App\Models\Configuracion;
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
        
        // Base query para reparaciones entregadas con comisión
        $query = Reparacion::with(['cliente', 'tecnico'])
            ->where('estado', 'entregado')
            ->whereNotNull('comision_monto')
            ->whereBetween('fecha_entrega', [$fechaDesde . ' 00:00:00', $fechaHasta . ' 23:59:59']);
        
        if ($tecnicoId) {
            $query->where('tecnico_id', $tecnicoId);
        }
        
        $reparaciones = $query->orderByDesc('fecha_entrega')->get();
        
        // Calcular totales por técnico
        $totalesPorTecnico = [];
        foreach ($reparaciones as $rep) {
            $tid = $rep->tecnico_id;
            if (!isset($totalesPorTecnico[$tid])) {
                $totalesPorTecnico[$tid] = [
                    'nombre' => $rep->tecnico->name ?? '—',
                    'total_reparado' => 0,
                    'comision_total' => 0,
                    'comision_pagada' => 0,
                    'comision_pendiente' => 0,
                    'cantidad' => 0,
                    'tecnico' => $rep->tecnico,
                ];
            }
            
            $montoReparado = $rep->costo_final !== null && $rep->costo_final > 0 
                ? (float)$rep->costo_final 
                : ((float)$rep->presupuesto ?? 0);
            
            $totalesPorTecnico[$tid]['total_reparado'] += $montoReparado;
            $totalesPorTecnico[$tid]['comision_total'] += (float)$rep->comision_monto;
            $totalesPorTecnico[$tid]['cantidad']++;
            
            if ($rep->comision_pagada) {
                $totalesPorTecnico[$tid]['comision_pagada'] += (float)$rep->comision_monto;
            } else {
                $totalesPorTecnico[$tid]['comision_pendiente'] += (float)$rep->comision_monto;
            }
        }
        
        // Configuración para el % global
        $empresa = Configuracion::empresa();
        $comisionGlobal = $empresa->comision_global_tecnicos ?? 0;
        
        return view('comisiones.index', compact(
            'tecnicos', 'tecnicoId', 'fechaDesde', 'fechaHasta',
            'reparaciones', 'totalesPorTecnico', 'comisionGlobal'
        ));
    }
    
    /**
     * Marcar una comisión como pagada
     */
    public function pagar(Request $request, Reparacion $reparacion)
    {
        $request->validate([
            'metodo_pago' => 'nullable|string|max:50',
        ]);
        
        $reparacion->update([
            'comision_pagada' => true,
            'comision_fecha_pago' => now(),
        ]);
        
        return back()->with('success', "Comisión de {$reparacion->numero_orden} marcada como pagada.");
    }
    
    /**
     * Marcar todas las comisiones de un técnico como pagadas
     */
    public function pagarTodo(Request $request, User $tecnico)
    {
        $request->validate([
            'metodo_pago' => 'nullable|string|max:50',
        ]);
        
        Reparacion::where('tecnico_id', $tecnico->id)
            ->where('estado', 'entregado')
            ->whereNotNull('comision_monto')
            ->where('comision_pagada', false)
            ->update([
                'comision_pagada' => true,
                'comision_fecha_pago' => now(),
            ]);
        
        return back()->with('success', "Todas las comisiones de {$tecnico->name} fueron marcadas como pagadas.");
    }
}