<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\User;
use App\Helpers\PaisHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CierreCajaController extends Controller
{
    /**
     * Lista de cierres de caja (histórico).
     */
    public function index(Request $request)
    {
        $query = CierreCaja::with('usuario');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        if ($request->filled('usuario_id')) {
            $query->where('user_id', $request->usuario_id);
        }

        $cierres = $query->orderByDesc('fecha_apertura')->paginate(15);

        $cajaAbierta = CierreCaja::cajaAbierta();
        $usuarios    = User::whereIn('rol', ['admin', 'vendedor'])->orderBy('name')->get();

        // Resumen general
        $totalVentasHoy = Venta::whereDate('fecha_venta', today())
            ->where('estado', 'completada')
            ->sum('total');

        $totalReparacionesHoy = Reparacion::whereDate('fecha_entrega', today())
            ->where('estado', 'entregado')
            ->sum('total');

        return view('caja.index', compact(
            'cierres', 'cajaAbierta', 'usuarios', 'totalVentasHoy', 'totalReparacionesHoy'
        ));
    }

    /**
     * Formulario para abrir caja.
     */
    public function crear()
    {
        if (CierreCaja::hayCajaAbierta()) {
            return redirect()->route('caja.index')
                ->with('error', 'Ya existe una caja abierta. Debe cerrarla antes de abrir una nueva.');
        }

        $ventasHoy = CierreCaja::ventasDelDia();
        $simbolo   = PaisHelper::configuracionActual()['simbolo_moneda'];

        return view('caja.abrir', compact('ventasHoy', 'simbolo'));
    }

    /**
     * Guarda la apertura de caja.
     */
    public function abrir(Request $request)
    {
        $validated = $request->validate([
            'monto_inicial' => 'required|numeric|min:0|max:99999999.99',
        ]);

        if (CierreCaja::hayCajaAbierta()) {
            return back()->with('error', 'Ya existe una caja abierta.');
        }

        DB::beginTransaction();
        try {
            $cierre = CierreCaja::create([
                'user_id'        => Auth::id(),
                'monto_inicial'  => $validated['monto_inicial'],
                'fecha_apertura' => now(),
                'estado'         => 'abierta',
            ]);

            // Registrar en auditoría
            \App\Helpers\AuditoriaHelper::registrar(
                'CierreCaja',
                'Apertura de caja',
                $cierre->id,
                "Caja #{$cierre->id} abierta con monto inicial {$cierre->monto_inicial}"
            );

            DB::commit();

            return redirect()->route('caja.index')
                ->with('success', "Caja abierta correctamente con S/ " . number_format($cierre->monto_inicial, 2) . ".");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al abrir la caja: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario de arqueo/cierre de caja.
     */
    public function cerrar()
    {
        $caja = CierreCaja::cajaAbierta();

        if (!$caja) {
            return redirect()->route('caja.index')
                ->with('error', 'No hay una caja abierta para cerrar.');
        }

        $ventasHoy = CierreCaja::ventasDelDia();
        $simbolo   = PaisHelper::configuracionActual()['simbolo_moneda'];

        // Totales del turno actual
        $ventasEfectivo    = $ventasHoy['efectivo'] ?? 0;
        $ventasTarjeta     = $ventasHoy['tarjeta'] ?? 0;
        $ventasTransferencia = $ventasHoy['transferencia'] ?? 0;
        $ventasOtros       = $ventasHoy['otros'] ?? 0;
        $totalIngresos     = $ventasHoy['total_ingresos'] ?? 0;
        $totalEgresos      = $ventasHoy['total_egresos'] ?? 0;

        $totalEsperado = (float)$caja->monto_inicial + (float)$ventasEfectivo - (float)$totalEgresos;

        return view('caja.cerrar', compact(
            'caja', 'ventasHoy', 'simbolo',
            'ventasEfectivo', 'ventasTarjeta', 'ventasTransferencia', 'ventasOtros',
            'totalIngresos', 'totalEgresos', 'totalEsperado'
        ));
    }

    /**
     * Procesa el cierre de caja con arqueo físico.
     */
    public function guardarCierre(Request $request)
    {
        $caja = CierreCaja::cajaAbierta();

        if (!$caja) {
            return back()->with('error', 'No hay una caja abierta.');
        }

        $validated = $request->validate([
            'total_contado'  => 'required|numeric|min:0|max:99999999.99',
            'observaciones'  => 'nullable|string|max:2000',
        ]);

        $ventasHoy = CierreCaja::ventasDelDia();

        $ventasEfectivo    = $ventasHoy['efectivo'] ?? 0;
        $ventasTarjeta     = $ventasHoy['tarjeta'] ?? 0;
        $ventasTransferencia = $ventasHoy['transferencia'] ?? 0;
        $ventasOtros       = $ventasHoy['otros'] ?? 0;
        $totalIngresos     = $ventasHoy['total_ingresos'] ?? 0;
        $totalEgresos      = $ventasHoy['total_egresos'] ?? 0;

        $totalEsperado = (float)$caja->monto_inicial + (float)$ventasEfectivo - (float)$totalEgresos;
        $totalContado  = (float)$validated['total_contado'];
        $diferencia    = round($totalContado - $totalEsperado, 2);

        DB::beginTransaction();
        try {
            $caja->update([
                'ventas_efectivo'   => $ventasEfectivo,
                'ventas_tarjeta'    => $ventasTarjeta,
                'ventas_transferencia' => $ventasTransferencia,
                'ventas_otros'      => $ventasOtros,
                'total_ingresos'    => $totalIngresos,
                'total_egresos'     => $totalEgresos,
                'total_esperado'    => $totalEsperado,
                'total_contado'     => $totalContado,
                'diferencia'        => $diferencia,
                'fecha_cierre'      => now(),
                'estado'            => 'cerrada',
                'observaciones'     => $validated['observaciones'] ?? null,
            ]);

            \App\Helpers\AuditoriaHelper::registrar(
                'CierreCaja',
                'Cierre de caja',
                $caja->id,
                "Caja #{$caja->id} cerrada. Esperado: {$totalEsperado}, Contado: {$totalContado}, Diferencia: {$diferencia}"
            );

            DB::commit();

            return redirect()->route('caja.show', $caja)
                ->with('success', 'Caja cerrada correctamente. Revisa el reporte de cierre.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cerrar la caja: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle de un cierre de caja.
     */
    public function show(CierreCaja $cierre)
    {
        $cierre->load('usuario');
        $simbolo = PaisHelper::configuracionActual()['simbolo_moneda'];

        return view('caja.show', compact('cierre', 'simbolo'));
    }

    /**
     * Imprime el ticket/corte de cierre de caja.
     */
    public function ticket(CierreCaja $cierre)
    {
        $cierre->load('usuario');
        $empresa = \App\Models\Configuracion::empresa();
        $simbolo = PaisHelper::configuracionActual()['simbolo_moneda'];

        $linea = str_repeat('─', 32);
        $lineaDoble = str_repeat('═', 32);

        $texto = $lineaDoble . "\n";
        $texto .= str_pad($empresa?->nombre_tienda ?? 'CRM Celulares', 32, ' ', STR_PAD_BOTH) . "\n";
        if ($empresa?->ruc) $texto .= str_pad('RUC: ' . $empresa->ruc, 32, ' ', STR_PAD_BOTH) . "\n";
        if ($empresa?->direccion) $texto .= str_pad($empresa->direccion, 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= $lineaDoble . "\n";
        $texto .= str_pad('CIERRE DE CAJA', 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= str_pad('#CC-' . str_pad($cierre->id, 6, '0', STR_PAD_LEFT), 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= $linea . "\n";

        $texto .= 'CAJERO: ' . ($cierre->usuario->name ?? '—') . "\n";
        $texto .= 'APERTURA: ' . $cierre->fecha_apertura?->format('d/m/Y H:i') . "\n";
        $texto .= 'CIERRE:   ' . $cierre->fecha_cierre?->format('d/m/Y H:i') . "\n";
        $texto .= $linea . "\n";

        $texto .= str_pad('MONTO INICIAL', 24) . str_pad($simbolo . ' ' . number_format($cierre->monto_inicial, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= str_pad('EFECTIVO', 24) . str_pad($simbolo . ' ' . number_format($cierre->ventas_efectivo, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= str_pad('TARJETA', 24) . str_pad($simbolo . ' ' . number_format($cierre->ventas_tarjeta, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= str_pad('TRANSFERENCIA', 24) . str_pad($simbolo . ' ' . number_format($cierre->ventas_transferencia, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= str_pad('OTROS', 24) . str_pad($simbolo . ' ' . number_format($cierre->ventas_otros, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= $linea . "\n";
        $texto .= str_pad('TOTAL INGRESOS', 24) . str_pad($simbolo . ' ' . number_format($cierre->total_ingresos, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= str_pad('TOTAL EGRESOS', 24) . str_pad('-' . $simbolo . ' ' . number_format($cierre->total_egresos, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= $lineaDoble . "\n";
        $texto .= str_pad('TOTAL ESPERADO', 24) . str_pad($simbolo . ' ' . number_format($cierre->total_esperado, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= str_pad('TOTAL CONTADO', 24) . str_pad($simbolo . ' ' . number_format($cierre->total_contado, 2), 8, ' ', STR_PAD_LEFT) . "\n";

        if ($cierre->diferencia > 0) {
            $texto .= str_pad('SOBRANTE', 24) . str_pad('+' . $simbolo . ' ' . number_format($cierre->diferencia, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        } elseif ($cierre->diferencia < 0) {
            $texto .= str_pad('FALTANTE', 24) . str_pad('-' . $simbolo . ' ' . number_format(abs($cierre->diferencia), 2), 8, ' ', STR_PAD_LEFT) . "\n";
        } else {
            $texto .= str_pad('CUADRADO', 24) . str_pad($simbolo . ' 0.00', 8, ' ', STR_PAD_LEFT) . "\n";
        }

        $texto .= $lineaDoble . "\n";

        if ($cierre->observaciones) {
            $texto .= "\nNOTAS: " . $cierre->observaciones . "\n";
        }

        $texto .= "\n" . str_pad('Firma Cajero: __________________', 32) . "\n";
        $texto .= str_pad('Firma Supervisor: ________________', 32) . "\n";
        $texto .= "\n" . str_pad('¡Gracias por usar el sistema!', 32, ' ', STR_PAD_BOTH) . "\n";

        return response($texto, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}