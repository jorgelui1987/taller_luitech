<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca']);

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo', 'like', "%{$request->buscar}%")
                  ->orWhere('modelo', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('condicion')) {
            $query->where('condicion', $request->condicion);
        }

        if ($request->filled('stock_bajo') && $request->stock_bajo) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        $productos   = $query->orderByDesc('created_at')->paginate(15);
        $categorias  = Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas      = Marca::where('activo', true)->orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias', 'marcas'));
    }

    public function create()
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas     = Marca::where('activo', true)->orderBy('nombre')->get();
        return view('productos.create', compact('categorias', 'marcas'));
    }

    public function store(Request $request)
    {
        $rules = [
            'tipo'          => 'required|in:celular,accesorio,otro',
            'codigo'        => 'required|string|unique:productos,codigo|max:50',
            'codigo_barras' => 'nullable|string|max:100|unique:productos,codigo_barras',
            'nombre'        => 'required|string|max:150',
            'descripcion'   => 'nullable|string',
            'garantia_dias' => 'nullable|integer|min:0',
            'categoria_id'  => 'required|exists:categorias,id',
            'marca_id'      => 'required|exists:marcas,id',
            'proveedor_id'  => 'nullable|exists:proveedores,id',
            'modelo'        => 'nullable|string|max:100',
            'color'         => 'nullable|string|max:50',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'imagen'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        // Campos solo para celulares
        if ($request->tipo === 'celular') {
            $rules['almacenamiento'] = 'nullable|string|max:20';
            $rules['ram']            = 'nullable|string|max:20';
            $rules['imei']           = 'nullable|string|max:20';
            $rules['condicion']      = 'required|in:nuevo,reacondicionado,usado';
        } else {
            $rules['condicion'] = 'nullable|in:nuevo,reacondicionado,usado';
        }

        $validated = $request->validate($rules);

        // Verificar límite de productos del plan
        $tenant = auth()->user()->tenant;
        if ($tenant && !$tenant->puedeAgregarProducto()) {
            return back()->with('error', 'Has alcanzado el límite de productos de tu plan (' . $tenant->max_productos . ').');
        }

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto registrado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'marca', 'detalleVentas.venta.cliente']);
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $marcas     = Marca::where('activo', true)->orderBy('nombre')->get();
        return view('productos.edit', compact('producto', 'categorias', 'marcas'));
    }

    public function update(Request $request, Producto $producto)
    {
        $rules = [
            'tipo'          => 'required|in:celular,accesorio,otro',
            'codigo'        => 'required|string|unique:productos,codigo,' . $producto->id . '|max:50',
            'nombre'        => 'required|string|max:150',
            'descripcion'   => 'nullable|string',
            'categoria_id'  => 'required|exists:categorias,id',
            'marca_id'      => 'required|exists:marcas,id',
            'modelo'        => 'nullable|string|max:100',
            'color'         => 'nullable|string|max:50',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'activo'        => 'boolean',
            'imagen'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        if ($request->tipo === 'celular') {
            $rules['almacenamiento'] = 'nullable|string|max:20';
            $rules['ram']            = 'nullable|string|max:20';
            $rules['imei']           = 'nullable|string|max:20';
            $rules['condicion']      = 'required|in:nuevo,reacondicionado,usado';
        } else {
            $rules['condicion'] = 'nullable|in:nuevo,reacondicionado,usado';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) Storage::disk('public')->delete($producto->imagen);
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto->update($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->detalleVentas()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: el producto tiene ventas registradas.');
        }

        if ($producto->imagen) Storage::disk('public')->delete($producto->imagen);
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Crear una marca rápidamente desde el modal del formulario
     */
    public function storeMarcaAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:marcas,nombre',
            ]);

            $marca = Marca::create([
                'nombre' => $validated['nombre'],
                'activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'id'      => $marca->id,
                'nombre'  => $marca->nombre,
                'message' => 'Marca creada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Crear una categoría rápidamente desde el modal del formulario
     */
    public function storeCategoriaAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100|unique:categorias,nombre',
            ]);

            $categoria = Categoria::create([
                'nombre' => $validated['nombre'],
                'activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'id'      => $categoria->id,
                'nombre'  => $categoria->nombre,
                'message' => 'Categoría creada correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 422);
        }
    }
}
