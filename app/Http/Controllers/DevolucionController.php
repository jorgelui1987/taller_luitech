<?php

namespace App\Http\Controllers;

use App\Models\Devolucion;
use App\Models\DevolucionDetalle;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\MovimientoStock;
use App\Models\Configuracion;
use App\Helpers\PaisHelper;
use App\Exceptions\DevolucionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DevolucionController extends Controller
{
    public function index(Request $request)
    {
        $query = Devolucion::with(['cliente', 'usuario', 'venta']);

        if ($request->filled('buscar')) {
            $query->where('numero_devolucion', 'like', "%{$request->buscar}%")
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

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_devolucion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_devolucion', '<=', $request->fecha_hasta);
        }

        $devoluciones = $query->orderByDesc('fecha_devolucion')->paginate(15);

        $totalMes = Devolucion::where('estado', 'completada')
            ->where('fecha_devolucion', '>=', Carbon::now()->startOfMonth())
            ->sum('total');

        return view('devoluciones.index', compact('devoluciones', 'totalMes'));
    }

    public function create()
    {
        $ventas = Venta::with(['cliente'])
            ->where('estado', 'completada')
            ->orderByDesc('fecha_venta')
            ->get();

        $ventaSeleccionada = null;
        if (request('venta_id')) {
            $ventaSeleccionada = Venta::with(['cliente', 'detalles.producto'])
                ->find(request('venta_id'));
        }

        return view('devoluciones.create', compact('ventas', 'ventaSeleccionada'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'venta_id'  => 'required|exists:ventas,id',
            'tipo'      => 'required|in:devolucion,garantia',
            'motivo'    => 'required|string|max:100',
            'tipo_reembolso' => 'nullable|in:efectivo,tarjeta,transferencia,nota_credito',
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

        if ($venta->estado !== 'completada') {
            return back()->with('error', 'Solo se pueden hacer devoluciones de ventas completadas.')->withInput();
        }

        // ⚠️ Bloquear devoluciones múltiples de la misma venta
        // Solo se permite UNA devolución por venta.
        $devolucionExistente = Devolucion::where('venta_id', $venta->id)
            ->where('estado', 'completada')
            ->exists();

        if ($devolucionExistente) {
            throw new DevolucionException('Esta venta ya tiene una devolución registrada. Solo se permite una devolución por venta.');
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $detalles = [];

            foreach ($request->productos as $item) {
                $detalleVenta = DetalleVenta::findOrFail($item['detalle_venta_id']);

                if ($detalleVenta->venta_id !== $venta->id) {
                    throw new DevolucionException('El producto no pertenece a la venta seleccionada.');
                }

                // Verificar que no se devuelva más de lo vendido
                $yaDevuelto = DevolucionDetalle::where('detalle_venta_id', $detalleVenta->id)
                    ->whereHas('devolucion', fn($q) => $q->where('estado', 'completada'))
                    ->sum('cantidad');

                $disponible = $detalleVenta->cantidad - $yaDevuelto;
                if ($item['cantidad'] > $disponible) {
                    throw new DevolucionException("No puedes devolver más de {$disponible} unidad(es) de este producto.");
                }

                $precioUnitario = $detalleVenta->precio_unitario;
                $descItem       = 0;
                $subItem        = $precioUnitario * $item['cantidad'];
                $subtotal      += $subItem;

                $detalles[] = [
                    'producto_id'     => $item['producto_id'],
                    'detalle_venta_id'=> $item['detalle_venta_id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'descuento'       => $descItem,
                    'subtotal'        => $subItem,
                    'condicion'       => $item['condicion'] ?? 'nuevo',
                ];

                // Reponer stock
                $producto = Producto::findOrFail($item['producto_id']);
                $stockAnterior = $producto->stock;
                $producto->increment('stock', $item['cantidad']);

                MovimientoStock::create([
                    'producto_id'    => $producto->id,
                    'tipo'           => 'entrada',
                    'motivo'         => 'devolucion',
                    'cantidad'       => $item['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $producto->fresh()->stock,
                    'observacion'    => "Devolución de {$item['cantidad']} unidad(es) - Venta {$venta->numero_venta}",
                    'user_id'        => Auth::id(),
                    'tenant_id'      => $tenantId,
                ]);
            }

            $igvPorcentaje = Configuracion::empresa()->igv ?? 18;
            $paisConfig = PaisHelper::configuracionActual();

            if (($paisConfig['pais'] ?? '') === 'CL') {
                // 🇨🇱 Chile: el precio YA INCLUYE IVA → descomponer
                $total     = round($subtotal, 2);
                $baseCL    = round($total / (1 + $igvPorcentaje / 100), 2);
                $impuesto  = round($total - $baseCL, 2);
            } else {
                // Otros países: el impuesto se SUMA
                $impuesto  = round($subtotal * ($igvPorcentaje / 100), 2);
                $total     = $subtotal + $impuesto;
            }

            $devolucion = Devolucion::create([
                'numero_devolucion' => Devolucion::generarNumero(),
                'venta_id'          => $venta->id,
                'cliente_id'        => $venta->cliente_id,
                'user_id'           => Auth::id(),
                'fecha_devolucion'  => now(),
                'motivo'            => $request->motivo,
                'tipo'              => $request->tipo,
                'estado'            => 'completada',
                'subtotal'          => $subtotal,
                'descuento'         => 0,
                'impuesto'          => $impuesto,
                'total'             => $total,
                'tipo_reembolso'    => $request->tipo_reembolso,
                'observacion'       => $request->observacion,
                'tenant_id'         => $tenantId,
            ]);

            foreach ($detalles as $detalle) {
                $detalle['devolucion_id'] = $devolucion->id;
                DevolucionDetalle::create($detalle);
            }

            // Marcar la venta como devuelta si todos los productos fueron devueltos
            $totalVendido = $venta->detalles->sum('cantidad');
            $totalDevuelto = DevolucionDetalle::whereHas('devolucion', fn($q) => $q->where('estado', 'completada'))
                ->whereIn('detalle_venta_id', $venta->detalles->pluck('id'))
                ->sum('cantidad');

            if ($totalDevuelto >= $totalVendido) {
                $venta->update(['estado' => 'devuelta']);
            }

            DB::commit();

            return redirect()->route('devoluciones.show', $devolucion)
                ->with('success', "Devolución {$devolucion->numero_devolucion} registrada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Devolucion $devolucion)
    {
        $devolucion->load(['cliente', 'usuario', 'venta', 'detalles.producto']);
        return view('devoluciones.show', compact('devolucion'));
    }

    public function anular(Devolucion $devolucion)
    {
        if ($devolucion->estado !== 'completada') {
            return back()->with('error', 'Solo se pueden anular devoluciones completadas.');
        }

        DB::transaction(function () use ($devolucion) {
            foreach ($devolucion->detalles as $detalle) {
                $producto = $detalle->producto;
                $stockAnterior = $producto->stock;
                $producto->decrement('stock', $detalle->cantidad);

                MovimientoStock::create([
                    'producto_id'    => $producto->id,
                    'tipo'           => 'salida',
                    'motivo'         => 'cancelacion',
                    'cantidad'       => -$detalle->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $producto->fresh()->stock,
                    'observacion'    => "Anulación de devolución {$devolucion->numero_devolucion}",
                    'user_id'        => Auth::id(),
                    'tenant_id'      => Auth::user()->tenant_id,
                ]);
            }

            $devolucion->update(['estado' => 'anulada']);

            // Restaurar estado de la venta si estaba marcada como devuelta
            $venta = $devolucion->venta;
            if ($venta && $venta->estado === 'devuelta') {
                $venta->update(['estado' => 'completada']);
            }
        });

        return back()->with('success', 'Devolución anulada y stock restaurado.');
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
            $yaDevuelto = DevolucionDetalle::where('detalle_venta_id', $det->id)
                ->whereHas('devolucion', fn($q) => $q->where('estado', 'completada'))
                ->sum('cantidad');

            $disponible = $det->cantidad - $yaDevuelto;

            $detalles[] = [
                'detalle_venta_id'    => $det->id,
                'producto_id'         => $det->producto_id,
                'producto_nombre'     => $det->producto->nombre ?? 'Producto eliminado',
                'cantidad_vendida'    => $det->cantidad,
                'cantidad_devuelta'   => $yaDevuelto,
                'cantidad_disponible' => $disponible,
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
