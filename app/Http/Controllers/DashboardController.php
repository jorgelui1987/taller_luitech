<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\Devolucion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();

        // ── ADMIN: Datos globales ─────────────────────────────────────────
        if ($user->esAdmin() || $user->esSuperAdmin()) {
            return $this->dashboardAdmin($hoy, $inicioMes, $inicioMesAnterior, $finMesAnterior);
        }

        // ── VENDEDOR: Solo sus ventas ─────────────────────────────────────
        if ($user->esVendedor()) {
            return $this->dashboardVendedor($user, $hoy, $inicioMes, $inicioMesAnterior, $finMesAnterior);
        }

        // ── TÉCNICO: Solo sus reparaciones y comisiones ───────────────────
        if ($user->esTecnico()) {
            return $this->dashboardTecnico($user, $inicioMes);
        }

        // Fallback: admin
        return $this->dashboardAdmin($hoy, $inicioMes, $inicioMesAnterior, $finMesAnterior);
    }

    /**
     * Dashboard para ADMIN: ve todo el negocio (global)
     */
    private function dashboardAdmin($hoy, $inicioMes, $inicioMesAnterior, $finMesAnterior)
    {
        $ventasHoy          = Venta::whereDate('fecha_venta', $hoy)->where('estado', 'completada')->sum('total');
        $ventasMes          = Venta::where('fecha_venta', '>=', $inicioMes)->where('estado', 'completada')->sum('total');
        $ventasMesAnterior  = Venta::whereBetween('fecha_venta', [$inicioMesAnterior, $finMesAnterior])->where('estado', 'completada')->sum('total');

        // Devoluciones del día y del mes (se restan de las ventas)
        $devolucionesHoy = Devolucion::whereDate('fecha_devolucion', $hoy)->where('estado', 'completada')->sum('total');
        $devolucionesMes = Devolucion::where('fecha_devolucion', '>=', $inicioMes)->where('estado', 'completada')->sum('total');
        $devolucionesMesAnterior = Devolucion::whereBetween('fecha_devolucion', [$inicioMesAnterior, $finMesAnterior])->where('estado', 'completada')->sum('total');

        $ventasHoy   = max(0, $ventasHoy - $devolucionesHoy);
        $ventasMes   = max(0, $ventasMes - $devolucionesMes);
        $ventasMesAnterior = max(0, $ventasMesAnterior - $devolucionesMesAnterior);

        // Restar costo de repuestos de reparaciones entregadas (ganancia real del negocio)
        $costoRepuestosHoy = Reparacion::where('estado', 'entregado')
            ->whereDate('fecha_entrega', $hoy)
            ->sum('costo_repuesto');
        $costoRepuestosMes = Reparacion::where('estado', 'entregado')
            ->where('fecha_entrega', '>=', $inicioMes)
            ->sum('costo_repuesto');
        $costoRepuestosMesAnterior = Reparacion::where('estado', 'entregado')
            ->whereBetween('fecha_entrega', [$inicioMesAnterior, $finMesAnterior])
            ->sum('costo_repuesto');

        // Ganancia real = Ventas - Comisiones (ventas negativas) - Costo de repuestos
        $ventasHoy   = max(0, $ventasHoy - $costoRepuestosHoy);
        $ventasMes   = max(0, $ventasMes - $costoRepuestosMes);
        $ventasMesAnterior = max(0, $ventasMesAnterior - $costoRepuestosMesAnterior);

        $crecimientoVentas = $ventasMesAnterior > 0 ? (($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100 : 0;

        $totalClientes    = Cliente::where('activo', true)->count();
        $clientesNuevosMes = Cliente::where('created_at', '>=', $inicioMes)->count();

        $totalProductos   = Producto::where('activo', true)->count();
        $stockBajo        = Producto::where('activo', true)->whereColumn('stock', '<=', 'stock_minimo')->count();

        $reparacionesPendientes = Reparacion::whereNotIn('estado', ['entregado', 'no_reparable'])->count();
        $reparacionesListas    = Reparacion::where('estado', 'listo')->count();

        // Gráfica de ventas por día (últimos 7 días)
        $diasSemana = $this->ventasPorDia(Venta::query());

        // Ventas por mes (últimos 6 meses)
        $ventasPorMes = $this->ventasPorMes(Venta::query());

        // Top 5 productos más vendidos
        $topProductos = $this->topProductos();

        // Últimas ventas
        $ultimasVentas = Venta::with(['cliente', 'vendedor'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Reparaciones recientes
        $ultimasReparaciones = Reparacion::with(['cliente', 'tecnico'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $miComisionPorcentaje = null;

        return view('dashboard.index', compact(
            'ventasHoy', 'ventasMes', 'crecimientoVentas',
            'totalClientes', 'clientesNuevosMes',
            'totalProductos', 'stockBajo',
            'reparacionesPendientes', 'reparacionesListas',
            'diasSemana', 'ventasPorMes', 'topProductos',
            'ultimasVentas', 'ultimasReparaciones',
            'miComisionPorcentaje'
        ));
    }

    /**
     * Dashboard para VENDEDOR: solo sus ventas
     */
    private function dashboardVendedor($user, $hoy, $inicioMes, $inicioMesAnterior, $finMesAnterior)
    {
        $ventasHoy          = Venta::where('user_id', $user->id)->whereDate('fecha_venta', $hoy)->where('estado', 'completada')->sum('total');
        $ventasMes          = Venta::where('user_id', $user->id)->where('fecha_venta', '>=', $inicioMes)->where('estado', 'completada')->sum('total');
        $ventasMesAnterior  = Venta::where('user_id', $user->id)->whereBetween('fecha_venta', [$inicioMesAnterior, $finMesAnterior])->where('estado', 'completada')->sum('total');

        // Devoluciones del día/mes del vendedor (restar de sus ventas)
        $devolucionesHoy = Devolucion::where('user_id', $user->id)->whereDate('fecha_devolucion', $hoy)->where('estado', 'completada')->sum('total');
        $devolucionesMes = Devolucion::where('user_id', $user->id)->where('fecha_devolucion', '>=', $inicioMes)->where('estado', 'completada')->sum('total');

        $ventasHoy = max(0, $ventasHoy - $devolucionesHoy);
        $ventasMes = max(0, $ventasMes - $devolucionesMes);

        $crecimientoVentas = $ventasMesAnterior > 0 ? (($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100 : 0;

        $misVentasHoy = Venta::where('user_id', $user->id)->whereDate('fecha_venta', $hoy)->where('estado', 'completada')->count();
        $misVentasMes = Venta::where('user_id', $user->id)->where('fecha_venta', '>=', $inicioMes)->where('estado', 'completada')->count();

        // Gráfica de mis ventas por día
        $diasSemana = $this->ventasPorDia(Venta::where('user_id', $user->id));

        // Mis ventas por mes
        $ventasPorMes = $this->ventasPorMes(Venta::where('user_id', $user->id));

        // Mis últimas ventas
        $ultimasVentas = Venta::with(['cliente', 'vendedor'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Variables vacías para la vista
        $totalClientes = 0;
        $clientesNuevosMes = 0;
        $totalProductos = 0;
        $stockBajo = 0;
        $reparacionesPendientes = 0;
        $reparacionesListas = 0;
        $topProductos = collect([]);
        $ultimasReparaciones = collect([]);
        $miComisionPorcentaje = null;

        return view('dashboard.index', compact(
            'ventasHoy', 'ventasMes', 'crecimientoVentas',
            'totalClientes', 'clientesNuevosMes',
            'totalProductos', 'stockBajo',
            'reparacionesPendientes', 'reparacionesListas',
            'diasSemana', 'ventasPorMes', 'topProductos',
            'ultimasVentas', 'ultimasReparaciones',
            'miComisionPorcentaje', 'misVentasHoy', 'misVentasMes'
        ));
    }

    /**
     * Dashboard para TÉCNICO: solo sus reparaciones y comisiones
     */
    private function dashboardTecnico($user, $inicioMes)
    {
        // Mis reparaciones activas (no entregadas)
        $misReparacionesActivas = Reparacion::where('tecnico_id', $user->id)
            ->whereNotIn('estado', ['entregado', 'no_reparable'])
            ->count();

        // Mis reparaciones listas para entregar
        $misReparacionesListas = Reparacion::where('tecnico_id', $user->id)
            ->where('estado', 'listo')
            ->count();

        // Mis reparaciones entregadas este mes
        $misEntregadasMes = Reparacion::where('tecnico_id', $user->id)
            ->where('estado', 'entregado')
            ->where('fecha_entrega', '>=', $inicioMes)
            ->count();

        // Mis comisiones pendientes
        $misComisionesPendientes = Reparacion::where('tecnico_id', $user->id)
            ->where('estado', 'entregado')
            ->where('comision_pagada', false)
            ->sum('comision_monto');

        // Mis comisiones pagadas
        $misComisionesPagadas = Reparacion::where('tecnico_id', $user->id)
            ->where('estado', 'entregado')
            ->where('comision_pagada', true)
            ->sum('comision_monto');

        // Mi porcentaje de comisión
        $miComisionPorcentaje = $user->comision_porcentaje;

        // Mis últimas reparaciones
        $ultimasReparaciones = Reparacion::with(['cliente', 'tecnico'])
            ->where('tecnico_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Variables vacías para la vista
        $ventasHoy = 0;
        $ventasMes = 0;
        $crecimientoVentas = 0;
        $totalClientes = 0;
        $clientesNuevosMes = 0;
        $totalProductos = 0;
        $stockBajo = 0;
        $reparacionesPendientes = 0;
        $reparacionesListas = 0;
        $diasSemana = collect([]);
        $ventasPorMes = collect([]);
        $topProductos = collect([]);
        $ultimasVentas = collect([]);

        return view('dashboard.index', compact(
            'ventasHoy', 'ventasMes', 'crecimientoVentas',
            'totalClientes', 'clientesNuevosMes',
            'totalProductos', 'stockBajo',
            'reparacionesPendientes', 'reparacionesListas',
            'diasSemana', 'ventasPorMes', 'topProductos',
            'ultimasVentas', 'ultimasReparaciones',
            'miComisionPorcentaje',
            'misReparacionesActivas', 'misReparacionesListas',
            'misEntregadasMes', 'misComisionesPendientes', 'misComisionesPagadas'
        ));
    }

    /**
     * Ventas por día (últimos 7 días)
     */
    private function ventasPorDia($query)
    {
        $driver = DB::connection()->getDriverName();
        $fechaExpr = $driver === 'pgsql'
            ? "TO_CHAR(fecha_venta, 'YYYY-MM-DD')"
            : "DATE(fecha_venta)";

        $ventasSemana = (clone $query)
            ->select(
                DB::raw("$fechaExpr as fecha"),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->where('fecha_venta', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->where('estado', 'completada')
            ->groupBy(DB::raw($fechaExpr))
            ->orderBy('fecha')
            ->get();

        $diasSemana = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::now()->subDays($i)->format('Y-m-d');
            $venta = $ventasSemana->firstWhere('fecha', $dia);
            $diasSemana->push([
                'fecha'    => Carbon::now()->subDays($i)->isoFormat('ddd D'),
                'total'    => $venta ? (float) $venta->total : 0,
                'cantidad' => $venta ? (int) $venta->cantidad : 0,
            ]);
        }

        return $diasSemana;
    }

    /**
     * Ventas por mes (últimos 6 meses)
     */
    private function ventasPorMes($query)
    {
        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'pgsql' ? "EXTRACT(YEAR FROM fecha_venta)" : "YEAR(fecha_venta)";
        $monthExpr = $driver === 'pgsql' ? "EXTRACT(MONTH FROM fecha_venta)" : "MONTH(fecha_venta)";

        return (clone $query)
            ->select(
                DB::raw("$yearExpr as anio"),
                DB::raw("$monthExpr as mes"),
                DB::raw('SUM(total) as total')
            )
            ->where('fecha_venta', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->where('estado', 'completada')
            ->groupBy(DB::raw($yearExpr), DB::raw($monthExpr))
            ->orderBy(DB::raw($yearExpr))
            ->orderBy(DB::raw($monthExpr))
            ->get()
            ->map(fn($v) => [
                'mes'   => Carbon::createFromDate($v->anio, $v->mes, 1)->isoFormat('MMM YY'),
                'total' => (float) $v->total,
            ]);
    }

    /**
     * Top 5 productos más vendidos
     */
    private function topProductos()
    {
        $inicioMes = Carbon::now()->startOfMonth();

        $query = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->where('ventas.fecha_venta', '>=', $inicioMes);

        // Aplicar filtro de tenant
        $user = auth()->user();
        if ($user && !$user->esSuperAdmin() && $user->tenant_id) {
            $query->where('ventas.tenant_id', $user->tenant_id);
        }

        return $query->select('productos.nombre', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'), DB::raw('SUM(detalle_ventas.subtotal) as ingresos'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();
    }
}
