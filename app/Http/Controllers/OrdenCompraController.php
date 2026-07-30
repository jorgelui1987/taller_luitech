<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\DetalleOrdenCompra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdenCompra::with(['proveedor', 'user']);

        if ($request->filled('buscar')) {
            $query->where('numero_orden', 'like', "%{$request->buscar}%");
        }

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_orden', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_orden', '<=', $request->fecha_hasta);
        }

        $ordenes = $query->orderByDesc('created_at')->paginate(15);
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();

        return view('compras.index', compact('ordenes', 'proveedores'));
    }

    public function create()
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'marca'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id'        => 'required|exists:proveedores,id',
            'fecha_estimada'      => 'nullable|date',
            'notas'               => 'nullable|string',
            'productos'           => 'required|array|min:1',
            'productos.*.id'      => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio'  => 'required|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0',
            'descuento_general'   => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $detalles = [];

            foreach ($request->productos as $item) {
                $descItem = (float)($item['descuento'] ?? 0);
                $subItem  = ((float)$item['precio'] * (int)$item['cantidad']) - $descItem;
                $subtotal += $subItem;

                $detalles[] = [
                    'producto_id'       => $item['id'],
                    'cantidad_ordenada' => $item['cantidad'],
                    'cantidad_recibida' => 0,
                    'precio_unitario'   => (float)$item['precio'],
                    'descuento'         => $descItem,
                    'subtotal'          => $subItem,
                ];
            }

            $descuento = (float)($request->descuento_general ?? 0);
            $base      = $subtotal - $descuento;
            $impuesto  = round($base * 0.18, 2);
            $total     = $base + $impuesto;

            $orden = OrdenCompra::create([
                'proveedor_id'  => $request->proveedor_id,
                'user_id'       => auth()->id(),
                'fecha_orden'   => now(),
                'fecha_estimada' => $request->fecha_estimada,
                'subtotal'      => $subtotal,
                'impuesto'      => $impuesto,
                'descuento'     => $descuento,
                'total'         => $total,
                'estado'        => 'pendiente',
                'notas'         => $request->notas,
                'tenant_id'     => auth()->user()->tenant_id,
            ]);

            foreach ($detalles as $detalle) {
                $detalle['orden_compra_id'] = $orden->id;
                $detalle['tenant_id'] = auth()->user()->tenant_id;
                DetalleOrdenCompra::create($detalle);
            }

            DB::commit();

            return redirect()->route('compras.show', $orden)
                ->with('success', "Orden de compra {$orden->numero_orden} creada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear orden: ' . $e->getMessage())->withInput();
        }
    }

    public function show(OrdenCompra $ordenCompra)
    {
        $ordenCompra->load(['proveedor', 'user', 'detalles.producto.marca']);
        return view('compras.show', compact('ordenCompra'));
    }

    public function edit(OrdenCompra $ordenCompra)
    {
        if (!in_array($ordenCompra->estado, ['pendiente', 'aprobada'])) {
            return back()->with('error', 'No se puede editar una orden ' . $ordenCompra->estado);
        }

        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'marca'])->where('activo', true)->orderBy('nombre')->get();

        return view('compras.edit', compact('ordenCompra', 'proveedores', 'productos'));
    }

    public function update(Request $request, OrdenCompra $ordenCompra)
    {
        if (!in_array($ordenCompra->estado, ['pendiente', 'aprobada'])) {
            return back()->with('error', 'No se puede actualizar una orden ' . $ordenCompra->estado);
        }

        // Similar a store pero actualizando
        // Por simplicidad, redirect a show con mensaje
        return redirect()->route('compras.show', $ordenCompra)
            ->with('info', 'Función de actualización disponible próximente.');
    }

    public function destroy(OrdenCompra $ordenCompra)
    {
        if ($ordenCompra->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden eliminar órdenes pendientes.');
        }

        $ordenCompra->detalles()->delete();
        $ordenCompra->delete();

        return redirect()->route('compras.index')
            ->with('success', 'Orden de compra eliminada.');
    }

    public function cambiarEstado(Request $request, OrdenCompra $ordenCompra)
    {
        $request->validate(['estado' => 'required|in:pendiente,aprobada,enviada,recibida_parcial,completada,cancelada']);

        DB::beginTransaction();
        try {
            $ordenCompra->update(['estado' => $request->estado]);

            // Si se marca como completada, recibir todo y actualizar stock
            if ($request->estado === 'completada') {
                foreach ($ordenCompra->detalles as $detalle) {
                    $pendiente = $detalle->cantidad_ordenada - $detalle->cantidad_recibida;
                    if ($pendiente > 0) {
                        $producto = $detalle->producto;
                        $stockAnterior = $producto->stock;
                        $producto->increment('stock', $pendiente);

                        $detalle->update([
                            'cantidad_recibida' => $detalle->cantidad_ordenada,
                        ]);

                        MovimientoStock::create([
                            'producto_id'    => $producto->id,
                            'tipo'           => 'entrada',
                            'motivo'         => 'compra',
                            'cantidad'       => $pendiente,
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo'    => $stockAnterior + $pendiente,
                            'observacion'    => "Orden compra {$ordenCompra->numero_orden} completada",
                            'user_id'        => auth()->id(),
                        ]);
                    }
                }
                $ordenCompra->update(['fecha_recibida' => now()]);
            }

            DB::commit();
            return back()->with('success', "Orden {$ordenCompra->numero_orden} actualizada a: {$request->estado}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}