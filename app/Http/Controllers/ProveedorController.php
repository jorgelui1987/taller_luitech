<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

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

        $proveedores = $query->orderBy('nombre')->paginate(15);
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
        $proveedor->load('ordenesCompra');
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
        if ($proveedor->ordenesCompra()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: el proveedor tiene órdenes de compra.');
        }

        $proveedor->delete();
        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    public function toggle(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => !$proveedor->activo]);
        return back()->with('success', 'Estado del proveedor actualizado.');
    }
}