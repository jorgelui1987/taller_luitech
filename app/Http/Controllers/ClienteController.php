<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Helpers\PaisHelper;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('apellido', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%")
                  ->orWhere('telefono', 'like', "%{$request->buscar}%")
                  ->orWhere('dni', 'like', "%{$request->buscar}%")
                  ->orWhere('rut', 'like', "%{$request->buscar}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $clientes = $query->withCount(['ventas', 'reparaciones'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'email'           => 'nullable|email|unique:clientes,email|max:150',
            'telefono'        => 'required|string|max:20',
            'celular'         => 'nullable|string|max:20',
            'dni'             => 'nullable|string|max:15|unique:clientes,dni',
            'rut'             => 'nullable|string|max:12',
            'rut_dv'          => 'nullable|string|max:1',
            'direccion'       => 'nullable|string|max:255',
            'ciudad'          => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'tipo'            => 'required|in:particular,empresa',
            'empresa'         => 'nullable|string|max:150',
            'ruc'             => 'nullable|string|max:15',
            'notas'           => 'nullable|string',
        ]);

        // Validar RUT chileno si se proporcionó
        if (!empty($validated['rut'])) {
            $paisConfig = PaisHelper::configuracionActual();
            if ($paisConfig['pais'] === 'CL') {
                $rutCompleto = $validated['rut'] . ($validated['rut_dv'] ?? '');
                if (!PaisHelper::validarRut($rutCompleto)) {
                    return back()->with('error', 'El RUT ingresado no es válido.')->withInput();
                }
            }
        }

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

        Cliente::create($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['ventas.detalles.producto', 'reparaciones']);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'email'           => 'nullable|email|unique:clientes,email,' . $cliente->id . '|max:150',
            'telefono'        => 'required|string|max:20',
            'celular'         => 'nullable|string|max:20',
            'dni'             => 'nullable|string|max:15|unique:clientes,dni,' . $cliente->id,
            'rut'             => 'nullable|string|max:12',
            'rut_dv'          => 'nullable|string|max:1',
            'direccion'       => 'nullable|string|max:255',
            'ciudad'          => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'tipo'            => 'required|in:particular,empresa',
            'empresa'         => 'nullable|string|max:150',
            'ruc'             => 'nullable|string|max:15',
            'notas'           => 'nullable|string',
            'activo'          => 'boolean',
        ]);

        // Validar RUT chileno si se proporcionó
        if (!empty($validated['rut'])) {
            $paisConfig = PaisHelper::configuracionActual();
            if ($paisConfig['pais'] === 'CL') {
                $rutCompleto = $validated['rut'] . ($validated['rut_dv'] ?? '');
                if (!PaisHelper::validarRut($rutCompleto)) {
                    return back()->with('error', 'El RUT ingresado no es válido.')->withInput();
                }
            }
        }

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->ventas()->count() > 0 || $cliente->reparaciones()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: el cliente tiene ventas o reparaciones registradas.');
        }

        $cliente->delete();
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }
}
