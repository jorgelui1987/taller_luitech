<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class StockBajoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca'])
            ->where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo');

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->filled('sin_stock') && $request->sin_stock) {
            $query->where('stock', '<=', 0);
        }

        $productos  = $query->orderBy('stock')->paginate(20);
        $categorias = \App\Models\Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas     = \App\Models\Marca::where('activo', true)->orderBy('nombre')->get();

        // Estadísticas
        $totalStockBajo  = Producto::where('activo', true)->whereColumn('stock', '<=', 'stock_minimo')->count();
        $totalSinStock   = Producto::where('activo', true)->where('stock', '<=', 0)->count();
        $valorStockBajo  = Producto::where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->get()
            ->sum(fn($p) => $p->stock * $p->precio_compra);
        $productosCriticos = Producto::where('activo', true)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->count();

        return view('stock.bajo', compact(
            'productos', 'categorias', 'marcas',
            'totalStockBajo', 'totalSinStock', 'valorStockBajo', 'productosCriticos'
        ));
    }

    public function notificacionWhatsApp(Producto $producto)
    {
        $mensaje = "🔴 *ALERTA DE STOCK BAJO* 🔴\n\n" .
                   "Producto: {$producto->nombre}\n" .
                   "Código: {$producto->codigo}\n" .
                   "Stock actual: {$producto->stock}\n" .
                   "Stock mínimo: {$producto->stock_minimo}\n" .
                   "Categoría: " . ($producto->categoria->nombre ?? '—') . "\n" .
                   "Marca: " . ($producto->marca->nombre ?? '—') . "\n\n" .
                   "⚠️ Es necesario reponer stock urgentemente.";

        // Obtener número de WhatsApp desde configuración
        $config = \App\Models\Configuracion::first();
        $telefono = $config->whatsapp_notificaciones ?? '';

        if (!$telefono) {
            return back()->with('error', 'No hay número configurado para notificaciones de WhatsApp. Configúralo en Ajustes.');
        }

        $url = "https://wa.me/" . preg_replace('/[^0-9]/', '', $telefono) . "?text=" . urlencode($mensaje);

        return redirect()->away($url);
    }

    public function apiContador()
    {
        $total = Producto::where('activo', true)->whereColumn('stock', '<=', 'stock_minimo')->count();
        $sinStock = Producto::where('activo', true)->where('stock', '<=', 0)->count();

        return response()->json([
            'total'    => $total,
            'sinStock' => $sinStock,
        ]);
    }
}