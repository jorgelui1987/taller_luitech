<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->desde)->startOfDay()
            : Carbon::now()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->hasta)->endOfDay()
            : Carbon::now()->endOfDay();

        // Período anterior para comparativa
        $dias = $desde->diffInDays($hasta) + 1;
        $desdeAnterior = (clone $desde)->subDays($dias);
        $hastaAnterior = (clone $desde)->subDay();

        // ── Resumen general ───────────────────────────────────────────────
        $totalVentas        = Venta::whereBetween('fecha_venta', [$desde, $hasta])->where('estado', 'completada')->sum('total');
        $totalVentasAnterior = Venta::whereBetween('fecha_venta', [$desdeAnterior, $hastaAnterior])->where('estado', 'completada')->sum('total');
        $crecimientoVentas  = $totalVentasAnterior > 0 ? (($totalVentas - $totalVentasAnterior) / $totalVentasAnterior) * 100 : 0;

        $cantidadVentas   = Venta::whereBetween('fecha_venta', [$desde, $hasta])->where('estado', 'completada')->count();
        $ticketPromedio   = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;
        $totalReparaciones = Reparacion::whereBetween('fecha_recepcion', [$desde, $hasta])->sum('costo_final');
        $clientesNuevos   = Cliente::whereBetween('created_at', [$desde, $hasta])->count();

        // ── Ganancias (margen bruto) ──────────────────────────────────────
        $ganancia = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$desde, $hasta])
            ->select(
                DB::raw('SUM((detalle_ventas.precio_unitario - productos.precio_compra) * detalle_ventas.cantidad - detalle_ventas.descuento) as ganancia'),
                DB::raw('SUM(productos.precio_compra * detalle_ventas.cantidad) as costo')
            )
            ->first();
        $gananciaTotal = $ganancia->ganancia ?? 0;
        $costoTotal    = $ganancia->costo ?? 0;
        $margenGeneral = $totalVentas > 0 ? ($gananciaTotal / $totalVentas) * 100 : 0;

        // ── IGV Total generado ────────────────────────────────────────────
        $igvTotal = Venta::whereBetween('fecha_venta', [$desde, $hasta])
            ->where('estado', 'completada')
            ->sum('impuesto');

        // ── Valor del inventario ──────────────────────────────────────────
        $valorInventarioCompra = Producto::where('activo', true)
            ->get()->sum(fn($p) => $p->stock * $p->precio_compra);
        $valorInventarioVenta  = Producto::where('activo', true)
            ->get()->sum(fn($p) => $p->stock * $p->precio_venta);
        $gananciaPotencial     = $valorInventarioVenta - $valorInventarioCompra;

        // ── Ventas por día ────────────────────────────────────────────────
        $ventasPorDia = Venta::select(
                DB::raw('DATE(fecha_venta) as fecha'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->where('estado', 'completada')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // ── Ventas por método de pago ──────────────────────────────────────
        $ventasPorPago = Venta::select('metodo_pago', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as monto'))
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->where('estado', 'completada')
            ->groupBy('metodo_pago')
            ->get();

        // ── Top 10 productos más vendidos ─────────────────────────────────
        $topProductos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$desde, $hasta])
            ->select(
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(detalle_ventas.cantidad) as unidades'),
                DB::raw('SUM(detalle_ventas.subtotal) as ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('ingresos')
            ->limit(10)
            ->get();

        // ── Top 10 clientes ───────────────────────────────────────────────
        $topClientes = DB::table('ventas')
            ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$desde, $hasta])
            ->select(
                'clientes.id',
                DB::raw("CONCAT(clientes.nombre,' ',clientes.apellido) as nombre"),
                DB::raw('COUNT(ventas.id) as compras'),
                DB::raw('SUM(ventas.total) as total')
            )
            ->groupBy('clientes.id', 'clientes.nombre', 'clientes.apellido')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // ── Reparaciones por estado ───────────────────────────────────────
        $repPorEstado = Reparacion::select('estado', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_recepcion', [$desde, $hasta])
            ->groupBy('estado')
            ->get();

        // ── Productos con stock bajo ───────────────────────────────────────
        $stockBajo = Producto::with(['categoria', 'marca'])
            ->where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->get();

        return view('reportes.index', compact(
            'totalVentas', 'cantidadVentas', 'ticketPromedio',
            'totalReparaciones', 'clientesNuevos',
            'crecimientoVentas', 'totalVentasAnterior',
            'gananciaTotal', 'costoTotal', 'margenGeneral',
            'igvTotal',
            'valorInventarioCompra', 'valorInventarioVenta', 'gananciaPotencial',
            'ventasPorDia', 'ventasPorPago',
            'topProductos', 'topClientes',
            'repPorEstado', 'stockBajo',
            'desde', 'hasta'
        ));
    }
}
