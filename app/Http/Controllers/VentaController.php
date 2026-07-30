<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\DetalleVenta;
use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'vendedor']);

        if ($request->filled('buscar')) {
            $query->where('numero_venta', 'like', "%{$request->buscar}%")
                  ->orWhereHas('cliente', fn($q) =>
                      $q->where('nombre', 'like', "%{$request->buscar}%")
                        ->orWhere('apellido', 'like', "%{$request->buscar}%")
                  );
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }

        $ventas = $query->orderByDesc('fecha_venta')->paginate(15);

        $totalMes = Venta::where('estado', 'completada')
            ->where('fecha_venta', '>=', Carbon::now()->startOfMonth())
            ->sum('total');

        return view('ventas.index', compact('ventas', 'totalMes'));
    }

    public function create()
    {
        $clientes  = Cliente::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'marca'])
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', compact('clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'          => 'nullable|exists:clientes,id',
            'metodo_pago'         => 'required|in:efectivo,tarjeta,transferencia,cuotas,yape,plin',
            'productos'           => 'required|array|min:1',
            'productos.*.id'      => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'descuento_general'   => 'nullable|numeric|min:0',
            'notas'               => 'nullable|string',
        ]);

        // Obtener tenant_id con fallback
        $tenantId = auth()->user()->tenant_id;
        if (!$tenantId) {
            $tenant = \App\Models\Tenant::first();
            if ($tenant) {
                $tenantId = $tenant->id;
                auth()->user()->update(['tenant_id' => $tenantId]);
            }
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $detalles = [];

            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['id']);

                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para: {$producto->nombre}");
                }

                $precioUnitario = $producto->precio_venta;
                $descItem       = isset($item['descuento']) ? (float)$item['descuento'] : 0;
                $subItem        = ($precioUnitario * $item['cantidad']) - $descItem;
                $subtotal      += $subItem;

                $detalles[] = [
                    'producto_id'    => $producto->id,
                    'cantidad'       => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'descuento'      => $descItem,
                    'subtotal'       => $subItem,
                    'imei_vendido'   => $item['imei'] ?? null,
                ];

                // Reducir stock y registrar movimiento
                $stockAnterior = $producto->stock;
                $producto->decrement('stock', $item['cantidad']);

                MovimientoStock::create([
                    'producto_id'    => $producto->id,
                    'tipo'           => 'salida',
                    'motivo'         => 'venta',
                    'cantidad'       => -$item['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $producto->fresh()->stock,
                    'observacion'    => "Venta de {$item['cantidad']} unidad(es)",
                    'user_id'        => Auth::id(),
                    'tenant_id'      => $tenantId,
                ]);
            }

            $descuento = (float)($request->descuento_general ?? 0);
            $base      = $subtotal - $descuento;
            $impuesto  = round($base * 0.18, 2);
            $total     = $base + $impuesto;

            $venta = Venta::create([
                'numero_venta' => Venta::generarNumero(),
                'cliente_id'   => $request->cliente_id,
                'user_id'      => Auth::id(),
                'fecha_venta'  => now(),
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'impuesto'     => $impuesto,
                'total'        => $total,
                'metodo_pago'  => $request->metodo_pago,
                'estado'       => 'completada',
                'notas'        => $request->notas,
                'tenant_id'    => $tenantId,
            ]);

            foreach ($detalles as $detalle) {
                $detalle['venta_id'] = $venta->id;
                DetalleVenta::create($detalle);
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', "Venta {$venta->numero_venta} registrada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Venta $venta)
    {
        $venta->load(['cliente', 'vendedor', 'detalles.producto.marca']);
        return view('ventas.show', compact('venta'));
    }

    public function printTicket(Venta $venta)
    {
        $venta->load(['cliente', 'vendedor', 'detalles.producto.marca']);
        return view('ventas.ticket', compact('venta'));
    }

    public function cancelar(Venta $venta)
    {
        if ($venta->estado !== 'completada') {
            return back()->with('error', 'Solo se pueden cancelar ventas completadas.');
        }

        DB::transaction(function () use ($venta) {
            foreach ($venta->detalles as $detalle) {
                $producto = $detalle->producto;
                $stockAnterior = $producto->stock;
                $producto->increment('stock', $detalle->cantidad);

                MovimientoStock::create([
                    'producto_id'    => $producto->id,
                    'tipo'           => 'entrada',
                    'motivo'         => 'cancelacion',
                    'cantidad'       => $detalle->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockAnterior + $detalle->cantidad,
                    'observacion'    => "Cancelación de venta {$venta->numero_venta}",
                    'user_id'        => Auth::id(),
                    'tenant_id'      => Auth::user()->tenant_id,
                ]);
            }
            $venta->update(['estado' => 'cancelada']);
        });

        return back()->with('success', 'Venta cancelada y stock restaurado.');
    }
}
