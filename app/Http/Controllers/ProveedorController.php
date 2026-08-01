<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('contacto', 'like', "%{$request->buscar}%")
                  ->orWhere('ruc', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === 'si');
        }

        $proveedores = $query->withCount('ordenesCompra')->orderBy('nombre')->paginate(15);
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'contacto'  => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'ruc'       => 'nullable|string|max:20',
            'notas'     => 'nullable|string',
        ]);

        // Forzar tenant_id
        $tenantId = auth()->user()->tenant_id;
        if (!$tenantId) {
            // Fallback: usar el primer tenant disponible
            $tenant = \App\Models\Tenant::first();
            if ($tenant) {
                $tenantId = $tenant->id;
                // Asignar al usuario para futuras operaciones
                auth()->user()->update(['tenant_id' => $tenantId]);
            } else {
                return back()->with('error', 'Error de configuración: no hay tenants en el sistema. Contacta al administrador.')->withInput();
            }
        }
        $validated['tenant_id'] = $tenantId;

        Proveedor::create($validated);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor registrado correctamente.');
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->loadCount('ordenesCompra');
        $proveedor->load(['ordenesCompra' => function ($query) {
            $query->orderByDesc('created_at');
        }]);
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'contacto'  => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'ruc'       => 'nullable|string|max:20',
            'notas'     => 'nullable|string',
            'activo'    => 'boolean',
        ]);

        $proveedor->update($validated);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        try {
            DB::beginTransaction();

            $proveedorId = $proveedor->id;

            // 1. Desvincular productos asociados a este proveedor (sin TenantScope)
            DB::table('productos')
                ->where('proveedor_id', $proveedorId)
                ->update(['proveedor_id' => null]);

            // 2. Obtener TODAS las órdenes de compra de este proveedor (sin TenantScope)
            $ordenIds = DB::table('ordenes_compra')
                ->where('proveedor_id', $proveedorId)
                ->pluck('id');

            if ($ordenIds->isNotEmpty()) {
                // 3. Eliminar detalles de esas órdenes de compra
                DB::table('detalle_ordenes_compra')
                    ->whereIn('orden_compra_id', $ordenIds)
                    ->delete();

                // 4. Eliminar las órdenes de compra
                DB::table('ordenes_compra')
                    ->whereIn('id', $ordenIds)
                    ->delete();
            }

            // 5. Eliminar el registro del proveedor
            DB::table('proveedores')
                ->where('id', $proveedorId)
                ->delete();

            DB::commit();

            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error al eliminar proveedor ID ' . $proveedor->id . ': ' . $e->getMessage());
            return redirect()->route('proveedores.index')
                ->with('error', 'Error al eliminar el proveedor: ' . $e->getMessage());
        }
    }

    public function toggle(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => !$proveedor->activo]);
        return back()->with('success', 'Estado del proveedor actualizado.');
    }
}