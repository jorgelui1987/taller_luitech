<?php

namespace App\Http\Controllers;

use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = MovimientoStock::with(['producto', 'user'])
            ->orderByDesc('created_at');

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('motivo')) {
            $query->where('motivo', $request->motivo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $movimientos = $query->paginate(30);
        $productos   = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('stock.movimientos', compact('movimientos', 'productos'));
    }

    public function ajusteForm()
    {
        $productos = Producto::with(['categoria', 'marca'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('stock.ajuste', compact('productos'));
    }

    public function ajusteStore(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo'        => 'required|in:entrada,salida,ajuste',
            'motivo'      => 'required|string|max:50',
            'cantidad'    => 'required|integer|min:1',
            'observacion' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::findOrFail($request->producto_id);
            $stockAnterior = $producto->stock;

            if ($request->tipo === 'salida' || ($request->tipo === 'ajuste' && $request->cantidad < 0)) {
                $cantidadFinal = -abs($request->cantidad);
            } else {
                $cantidadFinal = abs($request->cantidad);
            }

            $stockNuevo = $stockAnterior + $cantidadFinal;

            if ($stockNuevo < 0) {
                return back()->with('error', 'El stock no puede quedar negativo. Stock actual: ' . $stockAnterior)
                    ->withInput();
            }

            // Actualizar stock del producto
            $producto->update(['stock' => $stockNuevo]);

            // Registrar movimiento
            MovimientoStock::create([
                'producto_id'    => $producto->id,
                'tipo'           => $request->tipo,
                'motivo'         => $request->motivo,
                'cantidad'       => $cantidadFinal,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo'    => $stockNuevo,
                'observacion'    => $request->observacion,
                'user_id'        => auth()->id(),
            ]);

            DB::commit();

            $msg = $cantidadFinal > 0 ? 'entrada' : 'salida';
            return redirect()->route('stock.movimientos')
                ->with('success', "Ajuste de stock registrado correctamente. {$stockAnterior} → {$stockNuevo} (". abs($cantidadFinal) ." unidades de {$msg}).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al ajustar stock: ' . $e->getMessage())->withInput();
        }
    }

    public function porProducto(Producto $producto)
    {
        $movimientos = MovimientoStock::with('user')
            ->where('producto_id', $producto->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('stock.movimientos-producto', compact('producto', 'movimientos'));
    }
}