<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\OrdenCompra;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancieroController extends Controller
{
    /**
     * Dashboard financiero general
     */
    public function index(Request $request)
    {
        $year = $request->filled('year') ? (int)$request->year : Carbon::now()->year;
        $mes  = $request->filled('mes') ? (int)$request->mes : Carbon::now()->month;

        $fechaInicio = Carbon::create($year, $mes, 1)->startOfMonth();
        $fechaFin    = Carbon::create($year, $mes, 1)->endOfMonth();

        // ── INGRESOS DEL MES ────────────────────────────────────────────
        $ingresosVentas = Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')
            ->sum('total');

        $ingresosReparaciones = Reparacion::whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin])
            ->where('estado', 'entregado')
            ->sum('total');

        $totalIngresos = $ingresosVentas + $ingresosReparaciones;

        // ── COSTOS DEL MES ──────────────────────────────────────────────
        // Costo de ventas (productos vendidos)
        $costoVentas = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$fechaInicio, $fechaFin])
            ->sum(DB::raw('productos.precio_compra * detalle_ventas.cantidad'));

        // Costo de reparaciones (presupuesto como costo estimado)
        $costoReparaciones = Reparacion::whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['entregado', 'completado'])
            ->sum('costo_final');

        // Compras del mes (órdenes completadas/recibidas)
        $comprasMes = OrdenCompra::whereBetween('fecha_orden', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['completada', 'recibida_parcial'])
            ->sum('total');

        $totalCostos = $costoVentas + $costoReparaciones;

        // ── GANANCIA BRUTA Y NETA ───────────────────────────────────────
        $gananciaBruta  = $totalIngresos - $totalCostos;
        $margenBruto    = $totalIngresos > 0 ? ($gananciaBruta / $totalIngresos) * 100 : 0;

        // Gastos operativos estimados (podrían venir de una tabla de gastos)
        $gastosOperativos = $comprasMes; // Simplificación: compras como gasto operativo
        $gananciaNeta    = $gananciaBruta - $gastosOperativos;
        $margenNeto      = $totalIngresos > 0 ? ($gananciaNeta / $totalIngresos) * 100 : 0;

        // ── INDICADORES DE LIQUIDEZ ────────────────────────────────────
        // Efectivo estimado (ventas al contado)
        $efectivo = Venta::where('estado', 'completada')
            ->where('metodo_pago', 'efectivo')
            ->sum('total');

        $cuentasPorCobrar = Venta::where('estado', 'completada')
            ->where('metodo_pago', 'credito')
            ->sum('total');

        $cuentasPorPagar = OrdenCompra::whereIn('estado', ['pendiente', 'aprobada', 'enviada'])
            ->sum('total');

        // ── INVENTARIO ──────────────────────────────────────────────────
        $valorInventarioCompra = Producto::where('activo', true)
            ->get()->sum(fn($p) => $p->stock * $p->precio_compra);
        $valorInventarioVenta  = Producto::where('activo', true)
            ->get()->sum(fn($p) => $p->stock * $p->precio_venta);
        $gananciaPotencial     = $valorInventarioVenta - $valorInventarioCompra;

        // Rotación de inventario (costo ventas último año / inventario promedio)
        $costoVentasAnual = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada')
            ->where('ventas.fecha_venta', '>=', Carbon::now()->subYear())
            ->sum(DB::raw('productos.precio_compra * detalle_ventas.cantidad'));

        $rotacionInventario = $valorInventarioCompra > 0
            ? ($costoVentasAnual / $valorInventarioCompra)
            : 0;

        // ── SERIES TEMPORALES ──────────────────────────────────────────
        // Ingresos vs Costos por mes (últimos 12 meses)
        $serieMensual = collect();
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $inicio = (clone $m)->startOfMonth();
            $fin    = (clone $m)->endOfMonth();

            $ing = Venta::whereBetween('fecha_venta', [$inicio, $fin])
                    ->where('estado', 'completada')->sum('total')
                  + Reparacion::whereBetween('fecha_recepcion', [$inicio, $fin])
                    ->where('estado', 'entregado')->sum('total');

            $cos = DB::table('detalle_ventas')
                    ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
                    ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
                    ->where('ventas.estado', 'completada')
                    ->whereBetween('ventas.fecha_venta', [$inicio, $fin])
                    ->sum(DB::raw('productos.precio_compra * detalle_ventas.cantidad'));

            $serieMensual->push([
                'mes'    => $m->format('M Y'),
                'ingresos' => round($ing, 2),
                'costos'   => round($cos, 2),
                'ganancia' => round($ing - $cos, 2),
            ]);
        }

        // Ingresos por categoría de producto (top 8)
        $ingresosPorCategoria = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$fechaInicio, $fechaFin])
            ->select('categorias.nombre as categoria',
                DB::raw('SUM(detalle_ventas.subtotal) as total'),
                DB::raw('SUM(productos.precio_compra * detalle_ventas.cantidad) as costo'))
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // Métodos de pago
        $metodosPago = Venta::select('metodo_pago',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as monto'))
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')
            ->groupBy('metodo_pago')
            ->get();

        // ── KPIS ───────────────────────────────────────────────────────
        $kpis = [
            'totalIngresos'       => $totalIngresos,
            'totalCostos'         => $totalCostos,
            'gananciaBruta'       => $gananciaBruta,
            'margenBruto'         => round($margenBruto, 1),
            'gananciaNeta'        => $gananciaNeta,
            'margenNeto'          => round($margenNeto, 1),
            'ingresosVentas'      => $ingresosVentas,
            'ingresosReparaciones'=> $ingresosReparaciones,
            'costoVentas'         => $costoVentas,
            'comprasMes'          => $comprasMes,
            'efectivo'            => $efectivo,
            'cuentasPorCobrar'    => $cuentasPorCobrar,
            'cuentasPorPagar'     => $cuentasPorPagar,
            'capitalTrabajo'      => $efectivo + $cuentasPorCobrar - $cuentasPorPagar,
            'valorInventario'     => $valorInventarioCompra,
            'gananciaPotencial'   => $gananciaPotencial,
            'rotacionInventario'  => round($rotacionInventario, 1),
        ];

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $años = range(Carbon::now()->year - 3, Carbon::now()->year);

        return view('financiero.index', compact(
            'kpis', 'serieMensual', 'ingresosPorCategoria', 'metodosPago',
            'year', 'mes', 'meses', 'años', 'fechaInicio', 'fechaFin'
        ));
    }

    /**
     * Estado de Resultados (P&L)
     */
    public function estadoResultados(Request $request)
    {
        $year = $request->filled('year') ? (int)$request->year : Carbon::now()->year;
        $mes  = $request->filled('mes') ? (int)$request->mes : null;

        if ($mes) {
            $fechaInicio = Carbon::create($year, $mes, 1)->startOfMonth();
            $fechaFin    = Carbon::create($year, $mes, 1)->endOfMonth();
        } else {
            $fechaInicio = Carbon::create($year, 1, 1)->startOfYear();
            $fechaFin    = Carbon::create($year, 12, 31)->endOfYear();
        }

        // ── INGRESOS ────────────────────────────────────────────────────
        $ventas = Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')
            ->selectRaw('COUNT(*) as cantidad')
            ->selectRaw('SUM(subtotal) as subtotal')
            ->selectRaw('SUM(descuento) as descuento')
            ->selectRaw('SUM(impuesto) as impuesto')
            ->selectRaw('SUM(total) as total')
            ->first();

        $reparaciones = Reparacion::whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['entregado', 'completado'])
            ->selectRaw('COUNT(*) as cantidad')
            ->selectRaw('SUM(total) as total')
            ->first();

        $totalIngresos = ($ventas->total ?? 0) + ($reparaciones->total ?? 0);

        // ── COSTOS ──────────────────────────────────────────────────────
        $costoVentas = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$fechaInicio, $fechaFin])
            ->sum(DB::raw('productos.precio_compra * detalle_ventas.cantidad'));

        $costoReparaciones = Reparacion::whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['entregado', 'completado'])
            ->sum('costo_final');

        $totalCostos = $costoVentas + $costoReparaciones;

        $utilidadBruta  = $totalIngresos - $totalCostos;
        $margenBruto    = $totalIngresos > 0 ? ($utilidadBruta / $totalIngresos) * 100 : 0;

        // ── GASTOS OPERATIVOS ──────────────────────────────────────────
        // Compras/órdenes como gasto operativo
        $gastosAdmin = OrdenCompra::whereBetween('fecha_orden', [$fechaInicio, $fechaFin])
            ->whereIn('estado', ['completada', 'recibida_parcial'])
            ->sum('total');

        $totalGastosOperativos = $gastosAdmin;

        $utilidadOperativa   = $utilidadBruta - $totalGastosOperativos;
        $margenOperativo     = $totalIngresos > 0 ? ($utilidadOperativa / $totalIngresos) * 100 : 0;

        // ── GASTOS FINANCIEROS (simplificado) ──────────────────────────
        $gastosFinancieros = 0;

        $utilidadNeta    = $utilidadOperativa - $gastosFinancieros;
        $margenNeto      = $totalIngresos > 0 ? ($utilidadNeta / $totalIngresos) * 100 : 0;

        $periodo = $mes ? "$year - {$this->nombreMes($mes)}" : "Año $year";

        $meses = [];
        for ($i = 1; $i <= 12; $i++) {
            $meses[$i] = $this->nombreMes($i);
        }
        $años = range(Carbon::now()->year - 3, Carbon::now()->year);

        return view('financiero.estado-resultados', compact(
            'ventas', 'reparaciones',
            'totalIngresos', 'costoVentas', 'costoReparaciones', 'totalCostos',
            'utilidadBruta', 'margenBruto',
            'gastosAdmin', 'totalGastosOperativos',
            'utilidadOperativa', 'margenOperativo',
            'gastosFinancieros', 'utilidadNeta', 'margenNeto',
            'year', 'mes', 'periodo', 'fechaInicio', 'fechaFin',
            'meses', 'años'
        ));
    }

    /**
     * Balance General
     */
    public function balanceGeneral(Request $request)
    {
        $fechaCorte = $request->filled('fecha')
            ? Carbon::parse($request->fecha)->endOfDay()
            : Carbon::now()->endOfDay();

        // ── ACTIVOS ─────────────────────────────────────────────────────
        // Efectivo (ventas al contado)
        $efectivo = Venta::where('estado', 'completada')
            ->where('metodo_pago', 'efectivo')
            ->where('fecha_venta', '<=', $fechaCorte)
            ->sum('total');

        // Cuentas por cobrar (ventas al crédito)
        $cuentasPorCobrar = Venta::where('estado', 'completada')
            ->where('metodo_pago', 'credito')
            ->where('fecha_venta', '<=', $fechaCorte)
            ->sum('total');

        // Inventario
        $inventario = Producto::where('activo', true)
            ->get()->sum(fn($p) => $p->stock * $p->precio_compra);

        $totalActivoCorriente = $efectivo + $cuentasPorCobrar + $inventario;

        // Activo Fijo (simplificado, valor estimado)
        $activoFijo = $totalActivoCorriente * 0.3; // Estimación
        $totalActivos = $totalActivoCorriente + $activoFijo;

        // ── PASIVOS ─────────────────────────────────────────────────────
        $cuentasPorPagar = OrdenCompra::whereIn('estado', ['pendiente', 'aprobada', 'enviada'])
            ->where('fecha_orden', '<=', $fechaCorte)
            ->sum('total');

        $pasivosCortoPlazo = $cuentasPorPagar;
        $pasivosLargoPlazo = 0;
        $totalPasivos = $pasivosCortoPlazo + $pasivosLargoPlazo;

        // ── PATRIMONIO ──────────────────────────────────────────────────
        $capitalSocial = 0;
        $utilidadesRetenidas = $totalActivos - $totalPasivos - $capitalSocial;
        $totalPatrimonio = $capitalSocial + $utilidadesRetenidas;

        // Razones financieras
        $razonCorriente   = $totalPasivos > 0 ? round($totalActivoCorriente / $totalPasivos, 2) : 0;
        $pruebaAcida      = $totalPasivos > 0
            ? round(($totalActivoCorriente - $inventario) / $totalPasivos, 2)
            : 0;
        $endeudamiento    = $totalActivos > 0 ? round(($totalPasivos / $totalActivos) * 100, 1) : 0;

        $años = range(Carbon::now()->year - 3, Carbon::now()->year);

        return view('financiero.balance-general', compact(
            'efectivo', 'cuentasPorCobrar', 'inventario',
            'totalActivoCorriente', 'activoFijo', 'totalActivos',
            'cuentasPorPagar', 'pasivosCortoPlazo', 'pasivosLargoPlazo', 'totalPasivos',
            'capitalSocial', 'utilidadesRetenidas', 'totalPatrimonio',
            'razonCorriente', 'pruebaAcida', 'endeudamiento',
            'fechaCorte', 'años'
        ));
    }

    /**
     * Flujo de Caja
     */
    public function flujoCaja(Request $request)
    {
        $year = $request->filled('year') ? (int)$request->year : Carbon::now()->year;

        $mesesData = collect();
        $saldoInicial = 0;

        for ($m = 1; $m <= 12; $m++) {
            $inicio = Carbon::create($year, $m, 1)->startOfMonth();
            $fin    = Carbon::create($year, $m, 1)->endOfMonth();

            // Ingresos del mes
            $ingresosVentas = Venta::whereBetween('fecha_venta', [$inicio, $fin])
                ->where('estado', 'completada')->sum('total');

            $ingresosReparaciones = Reparacion::whereBetween('fecha_recepcion', [$inicio, $fin])
                ->where('estado', 'entregado')->sum('total');

            $totalIngresos = $ingresosVentas + $ingresosReparaciones;

            // Egresos del mes
            $compras = OrdenCompra::whereBetween('fecha_orden', [$inicio, $fin])
                ->whereIn('estado', ['completada', 'recibida_parcial'])
                ->sum('total');

            $totalEgresos = $compras;

            $saldoMensual   = $totalIngresos - $totalEgresos;
            $saldoAcumulado = $saldoInicial + $saldoMensual;

            $mesesData->push([
                'mes'          => $this->nombreMes($m),
                'ingresos'     => round($totalIngresos, 2),
                'egresos'      => round($totalEgresos, 2),
                'saldo_mes'    => round($saldoMensual, 2),
                'saldo_acum'   => round($saldoAcumulado, 2),
            ]);

            $saldoInicial = $saldoAcumulado;
        }

        $totalIngresosAnual = $mesesData->sum('ingresos');
        $totalEgresosAnual  = $mesesData->sum('egresos');
        $saldoFinal         = $mesesData->last()['saldo_acum'] ?? 0;

        $años = range(Carbon::now()->year - 3, Carbon::now()->year);

        return view('financiero.flujo-caja', compact(
            'mesesData', 'totalIngresosAnual', 'totalEgresosAnual',
            'saldoFinal', 'year', 'años'
        ));
    }

    /**
     * Indicadores Financieros
     */
    public function indicadores(Request $request)
    {
        $year = $request->filled('year') ? (int)$request->year : Carbon::now()->year;

        $fechaInicio = Carbon::create($year, 1, 1)->startOfYear();
        $fechaFin    = Carbon::create($year, 12, 31)->endOfYear();

        // ── RENTABILIDAD ────────────────────────────────────────────────
        $totalVentas = Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')->sum('total');

        $costoVentas = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$fechaInicio, $fechaFin])
            ->sum(DB::raw('productos.precio_compra * detalle_ventas.cantidad'));

        $gananciaBruta   = $totalVentas - $costoVentas;
        $margenBruto     = $totalVentas > 0 ? ($gananciaBruta / $totalVentas) * 100 : 0;

        $totalReparaciones = Reparacion::whereBetween('fecha_recepcion', [$fechaInicio, $fechaFin])
            ->where('estado', 'entregado')->sum('total');

        $totalIngresos = $totalVentas + $totalReparaciones;

        // ── LIQUIDEZ ────────────────────────────────────────────────────
        $efectivo = Venta::where('estado', 'completada')
            ->where('metodo_pago', 'efectivo')
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->sum('total');

        $cuentasPorCobrar = Venta::where('estado', 'completada')
            ->where('metodo_pago', 'credito')
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->sum('total');

        $inventario = Producto::where('activo', true)
            ->get()->sum(fn($p) => $p->stock * $p->precio_compra);

        $cuentasPorPagar = OrdenCompra::whereIn('estado', ['pendiente', 'aprobada', 'enviada'])
            ->sum('total');

        $razonCorriente = $cuentasPorPagar > 0
            ? round(($efectivo + $cuentasPorCobrar + $inventario) / $cuentasPorPagar, 2)
            : 0;

        $pruebaAcida = $cuentasPorPagar > 0
            ? round(($efectivo + $cuentasPorCobrar) / $cuentasPorPagar, 2)
            : 0;

        // ── EFICIENCIA ──────────────────────────────────────────────────
        $rotacionInventario = $inventario > 0
            ? round($costoVentas / $inventario, 1)
            : 0;

        $diasInventario = $rotacionInventario > 0
            ? round(365 / $rotacionInventario, 0)
            : 0;

        $numVentas = Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada')->count();

        $ticketPromedio = $numVentas > 0 ? round($totalVentas / $numVentas, 2) : 0;

        // ── ENDEUDAMIENTO ───────────────────────────────────────────────
        $totalActivos = $efectivo + $cuentasPorCobrar + $inventario;
        $endeudamiento = $totalActivos > 0
            ? round(($cuentasPorPagar / $totalActivos) * 100, 1)
            : 0;

        // ── PRODUCTIVIDAD ───────────────────────────────────────────────
        $numProductosVendidos = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha_venta', [$fechaInicio, $fechaFin])
            ->sum('detalle_ventas.cantidad');

        // Evolución mensual de ingresos para gráfico
        $evolucionMensual = collect();
        for ($i = 1; $i <= 12; $i++) {
            $inicio = Carbon::create($year, $i, 1)->startOfMonth();
            $fin    = Carbon::create($year, $i, 1)->endOfMonth();

            $ing = Venta::whereBetween('fecha_venta', [$inicio, $fin])
                ->where('estado', 'completada')->sum('total');

            $cos = DB::table('detalle_ventas')
                ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
                ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
                ->where('ventas.estado', 'completada')
                ->whereBetween('ventas.fecha_venta', [$inicio, $fin])
                ->sum(DB::raw('productos.precio_compra * detalle_ventas.cantidad'));

            $evolucionMensual->push([
                'mes'      => $this->nombreMes($i),
                'ingresos' => round($ing, 2),
                'costos'   => round($cos, 2),
            ]);
        }

        $años = range(Carbon::now()->year - 3, Carbon::now()->year);

        $indicadores = compact(
            'margenBruto', 'gananciaBruta',
            'razonCorriente', 'pruebaAcida',
            'rotacionInventario', 'diasInventario', 'ticketPromedio',
            'endeudamiento', 'numVentas', 'numProductosVendidos',
            'totalVentas', 'totalReparaciones', 'totalIngresos', 'costoVentas',
            'efectivo', 'cuentasPorCobrar', 'inventario', 'cuentasPorPagar',
            'totalActivos'
        );

        return view('financiero.indicadores', compact(
            'indicadores', 'evolucionMensual', 'year', 'años'
        ));
    }

    private function nombreMes(int $numero): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return $meses[$numero] ?? 'Desconocido';
    }
}