<?php

namespace App\Http\Controllers;

use App\Models\Garantia;
use App\Models\GarantiaDetalle;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GarantiaController extends Controller
{
    public function index(Request $request)
    {
        $query = Garantia::with(['cliente', 'usuario', 'venta']);

        if ($request->filled('buscar')) {
            $query->where('numero_garantia', 'like', "%{$request->buscar}%")
                  ->orWhereHas('venta', fn($q) =>
                      $q->where('numero_venta', 'like', "%{$request->buscar}%")
                  )
                  ->orWhereHas('cliente', fn($q) =>
                      $q->where('nombre', 'like', "%{$request->buscar}%")
                        ->orWhere('apellido', 'like', "%{$request->buscar}%")
                  );
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_garantia', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_garantia', '<=', $request->fecha_hasta);
        }

        $garantias = $query->orderByDesc('fecha_garantia')->paginate(15);

        $totalMes = Garantia::where('estado', 'completada')
            ->where('fecha_garantia', '>=', Carbon::now()->startOfMonth())
            ->count();

        return view('garantias.index', compact('garantias', 'totalMes'));
    }

    public function create()
    {
        $ventas = Venta::with(['cliente'])
            ->whereIn('estado', ['completada', 'devuelta'])
            ->orderByDesc('fecha_venta')
            ->get();

        $ventaSeleccionada = null;
        if (request('venta_id')) {
            $ventaSeleccionada = Venta::with(['cliente', 'detalles.producto'])
                ->find(request('venta_id'));
        }

        return view('garantias.create', compact('ventas', 'ventaSeleccionada'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'venta_id'  => 'required|exists:ventas,id',
            'motivo'    => 'required|string|max:100',
            'observacion'    => 'nullable|string|max:1000',
            'productos'      => 'required|array|min:1|max:100',
            'productos.*.detalle_venta_id' => 'required|exists:detalle_ventas,id',
            'productos.*.producto_id'      => 'required|exists:productos,id',
            'productos.*.cantidad'         => 'required|integer|min:1|max:1000',
            'productos.*.condicion'        => 'nullable|in:nuevo,usado,dañado,incompleto',
        ]);

        $tenantId = auth()->user()->tenant_id;
        if (!$tenantId) {
            $tenant = \App\Models\Tenant::first();
            if ($tenant) {
                $tenantId = $tenant->id;
                auth()->user()->update(['tenant_id' => $tenantId]);
            }
        }

        $venta = Venta::with(['detalles'])->findOrFail($request->venta_id);

        if (!in_array($venta->estado, ['completada', 'devuelta'])) {
            return back()->with('error', 'Solo se pueden registrar garantías de ventas completadas.')->withInput();
        }

        DB::beginTransaction();
        try {
            $detalles = [];

            foreach ($request->productos as $item) {
                $detalleVenta = DetalleVenta::findOrFail($item['detalle_venta_id']);

                if ($detalleVenta->venta_id !== $venta->id) {
                    throw new \Exception('El producto no pertenece a la venta seleccionada.');
                }

                $condicion = $item['condicion'] ?? 'nuevo';
                $producto = Producto::findOrFail($item['producto_id']);

                $detalles[] = [
                    'producto_id'     => $item['producto_id'],
                    'detalle_venta_id'=> $item['detalle_venta_id'],
                    'cantidad'        => $item['cantidad'],
                    'condicion'       => $condicion,
                ];

                if (in_array($condicion, ['nuevo', 'usado'])) {
                    $stockAnterior = $producto->stock;
                    $producto->increment('stock', $item['cantidad']);

                    MovimientoStock::create([
                        'producto_id'    => $producto->id,
                        'tipo'           => 'entrada',
                        'motivo'         => 'garantia',
                        'cantidad'       => $item['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo'    => $producto->fresh()->stock,
                        'observacion'    => "Garantía de {$item['cantidad']} unidad(es) - Venta {$venta->numero_venta} (condición: {$condicion})",
                        'user_id'        => Auth::id(),
                        'tenant_id'      => $tenantId,
                    ]);
                } else {
                    $stockDaniadoAnterior = (int) $producto->stock_daniado;
                    $producto->increment('stock_daniado', $item['cantidad']);

                    MovimientoStock::create([
                        'producto_id'    => $producto->id,
                        'tipo'           => 'entrada',
                        'motivo'         => 'garantia_daniado',
                        'cantidad'       => $item['cantidad'],
                        'stock_anterior' => $stockDaniadoAnterior,
                        'stock_nuevo'    => (int) $producto->fresh()->stock_daniado,
                        'observacion'    => "Garantía de {$item['cantidad']} unidad(es) DAÑADA(S) - Venta {$venta->numero_venta} (condición: {$condicion})",
                        'user_id'        => Auth::id(),
                        'tenant_id'      => $tenantId,
                    ]);
                }
            }

            $garantia = Garantia::create([
                'numero_garantia' => Garantia::generarNumero(),
                'venta_id'        => $venta->id,
                'cliente_id'      => $venta->cliente_id,
                'user_id'         => Auth::id(),
                'fecha_garantia'  => now(),
                'motivo'          => $request->motivo,
                'estado'          => 'completada',
                'observacion'     => $request->observacion,
                'tenant_id'       => $tenantId,
            ]);

            foreach ($detalles as $detalle) {
                $detalle['garantia_id'] = $garantia->id;
                GarantiaDetalle::create($detalle);
            }

            DB::commit();

            return redirect()->route('garantias.show', $garantia)
                ->with('success', "Garantía {$garantia->numero_garantia} registrada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Garantia $garantia)
    {
        $garantia->load(['cliente', 'usuario', 'venta', 'detalles.producto']);
        return view('garantias.show', compact('garantia'));
    }

    public function anular(Garantia $garantia)
    {
        if ($garantia->estado !== 'completada') {
            return back()->with('error', 'Solo se pueden anular garantías completadas.');
        }

        DB::transaction(function () use ($garantia) {
            foreach ($garantia->detalles as $detalle) {
                $producto = $detalle->producto;

                if (in_array($detalle->condicion, ['nuevo', 'usado'])) {
                    $stockAnterior = $producto->stock;
                    $producto->decrement('stock', $detalle->cantidad);

                    MovimientoStock::create([
                        'producto_id'    => $producto->id,
                        'tipo'           => 'salida',
                        'motivo'         => 'cancelacion_garantia',
                        'cantidad'       => -$detalle->cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo'    => $producto->fresh()->stock,
                        'observacion'    => "Anulación de garantía {$garantia->numero_garantia} (condición: {$detalle->condicion})",
                        'user_id'        => Auth::id(),
                        'tenant_id'      => Auth::user()->tenant_id,
                    ]);
                } else {
                    $stockDaniadoAnterior = (int) $producto->stock_daniado;
                    $producto->decrement('stock_daniado', $detalle->cantidad);

                    MovimientoStock::create([
                        'producto_id'    => $producto->id,
                        'tipo'           => 'salida',
                        'motivo'         => 'cancelacion_garantia_daniado',
                        'cantidad'       => -$detalle->cantidad,
                        'stock_anterior' => $stockDaniadoAnterior,
                        'stock_nuevo'    => (int) $producto->fresh()->stock_daniado,
                        'observacion'    => "Anulación de garantía {$garantia->numero_garantia} (dañado, condición: {$detalle->condicion})",
                        'user_id'        => Auth::id(),
                        'tenant_id'      => Auth::user()->tenant_id,
                    ]);
                }
            }

            $garantia->update(['estado' => 'anulada']);
        });

        return back()->with('success', 'Garantía anulada y stock restaurado.');
    }

    public function getVentaDetalles($ventaId)
    {
        $venta = Venta::with(['cliente', 'detalles.producto'])->find($ventaId);

        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada.'], 404);
        }

        if ($venta->estado !== 'completada' && $venta->estado !== 'devuelta') {
            return response()->json(['error' => 'La venta no está completada.'], 400);
        }

        $detalles = [];
        foreach ($venta->detalles as $det) {
            $detalles[] = [
                'detalle_venta_id'    => $det->id,
                'producto_id'         => $det->producto_id,
                'producto_nombre'     => $det->producto->nombre ?? 'Producto eliminado',
                'cantidad_vendida'    => $det->cantidad,
                'precio_unitario'     => (float)$det->precio_unitario,
                'imei'                => $det->imei_vendido,
            ];
        }

        return response()->json([
            'venta' => [
                'id'           => $venta->id,
                'numero_venta' => $venta->numero_venta,
                'fecha_venta'  => $venta->fecha_venta->format('d/m/Y H:i'),
                'cliente'      => $venta->cliente?->nombre_completo ?? 'Venta general',
                'total'        => (float)$venta->total,
            ],
            'detalles' => $detalles,
        ]);
    }
}