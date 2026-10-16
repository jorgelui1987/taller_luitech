<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Helpers\PaisHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
                  ->orWhere('celular', 'like', "%{$request->buscar}%")
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
        // Forzar tenant_id primero (se necesita para validar unicidad por taller)
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

        // Normalizar WhatsApp: solo dígitos (el código país lo agrega WhatsAppHelper al enviar)
        $request->merge([
            'telefono' => preg_replace('/\D/', '', (string) $request->input('telefono', '')),
            'celular'  => $request->filled('celular') ? preg_replace('/\D/', '', (string) $request->input('celular')) : $request->input('celular'),
            'dni'      => $request->filled('dni') ? trim((string) $request->input('dni')) : null,
        ]);

        $validated = $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'email'           => 'nullable|email|max:150|' . Rule::unique('clientes', 'email')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            // WhatsApp es la identificación del cliente: obligatorio y único por taller
            'telefono'        => ['required', 'string', 'max:20', Rule::unique('clientes', 'telefono')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'celular'         => 'nullable|string|max:20',
            // RUT/DNI opcional: solo se pide si el cliente quiere factura o lo entrega voluntariamente
            'dni'             => ['nullable', 'string', 'max:15', Rule::unique('clientes', 'dni')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'rut'             => 'nullable|string|max:12',
            'rut_dv'          => 'nullable|string|max:1',
            'direccion'       => 'nullable|string|max:255',
            'ciudad'          => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'tipo'            => 'required|in:particular,empresa',
            'empresa'         => 'nullable|string|max:150',
            'ruc'             => 'nullable|string|max:15',
            'notas'           => 'nullable|string',
        ], [
            'telefono.required' => 'El WhatsApp es obligatorio: es la identificación del cliente.',
            'telefono.unique'   => 'Ese WhatsApp ya está registrado para otro cliente de tu taller.',
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
        // Normalizar WhatsApp: solo dígitos
        $request->merge([
            'telefono' => preg_replace('/\D/', '', (string) $request->input('telefono', '')),
            'celular'  => $request->filled('celular') ? preg_replace('/\D/', '', (string) $request->input('celular')) : $request->input('celular'),
            'dni'      => $request->filled('dni') ? trim((string) $request->input('dni')) : null,
        ]);

        $tenantId = $cliente->tenant_id ?? auth()->user()->tenant_id;

        $validated = $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'required|string|max:100',
            'email'           => ['nullable', 'email', 'max:150', Rule::unique('clientes', 'email')->where(fn ($q) => $q->where('tenant_id', $tenantId))->ignore($cliente->id)],
            // WhatsApp es la identificación del cliente: obligatorio y único por taller
            'telefono'        => ['required', 'string', 'max:20', Rule::unique('clientes', 'telefono')->where(fn ($q) => $q->where('tenant_id', $tenantId))->ignore($cliente->id)],
            'celular'         => 'nullable|string|max:20',
            // RUT/DNI opcional
            'dni'             => ['nullable', 'string', 'max:15', Rule::unique('clientes', 'dni')->where(fn ($q) => $q->where('tenant_id', $tenantId))->ignore($cliente->id)],
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
        ], [
            'telefono.required' => 'El WhatsApp es obligatorio: es la identificación del cliente.',
            'telefono.unique'   => 'Ese WhatsApp ya está registrado para otro cliente de tu taller.',
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
