<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    // ── Vista principal ─────────────────────────────────────────────────
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // 1. Usuarios del mismo tenant (excluyendo superadmin)
        $usuarios = User::where('tenant_id', $tenantId)
            ->where('rol', '!=', 'superadmin')
            ->orderBy('rol')
            ->orderBy('name')
            ->get();

        // 2. Datos de la empresa
        $empresa = Configuracion::empresa();

        // 3. Estadísticas del sistema (solo de este tenant)
        $stats = [
            ['icon' => 'users',          'color' => '#a855f7', 'label' => 'Usuarios activos',          'value' => User::where('tenant_id', $tenantId)->where('activo', true)->count()],
            ['icon' => 'users',          'color' => '#06b6d4', 'label' => 'Total clientes',            'value' => Cliente::where('tenant_id', $tenantId)->count()],
            ['icon' => 'box',            'color' => '#10b981', 'label' => 'Productos en inventario',   'value' => Producto::where('tenant_id', $tenantId)->where('activo', true)->count()],
            ['icon' => 'shopping-cart',  'color' => '#ec4899', 'label' => 'Ventas registradas',        'value' => Venta::where('tenant_id', $tenantId)->count()],
            ['icon' => 'tools',          'color' => '#f59e0b', 'label' => 'Órdenes de reparación',     'value' => Reparacion::where('tenant_id', $tenantId)->count()],
        ];

        // 4. Lista de monedas disponibles
        $monedas = [
            'PEN' => ['simbolo' => 'S/.', 'pais' => 'Perú'],
            'USD' => ['simbolo' => '$',    'pais' => 'Estados Unidos'],
            'EUR' => ['simbolo' => '€',    'pais' => 'Unión Europea'],
            'MXN' => ['simbolo' => '$',    'pais' => 'México'],
            'COP' => ['simbolo' => '$',    'pais' => 'Colombia'],
            'CLP' => ['simbolo' => '$',    'pais' => 'Chile'],
            'ARS' => ['simbolo' => '$',    'pais' => 'Argentina'],
            'BOB' => ['simbolo' => 'Bs.',  'pais' => 'Bolivia'],
            'UYU' => ['simbolo' => '$U',   'pais' => 'Uruguay'],
            'PYG' => ['simbolo' => '₲',    'pais' => 'Paraguay'],
            'VES' => ['simbolo' => 'Bs.',  'pais' => 'Venezuela'],
            'CRC' => ['simbolo' => '₡',    'pais' => 'Costa Rica'],
            'GTQ' => ['simbolo' => 'Q',    'pais' => 'Guatemala'],
            'HNL' => ['simbolo' => 'L',    'pais' => 'Honduras'],
            'NIO' => ['simbolo' => 'C$',   'pais' => 'Nicaragua'],
            'PAB' => ['simbolo' => 'B/.',  'pais' => 'Panamá'],
            'DOP' => ['simbolo' => 'RD$',  'pais' => 'República Dominicana'],
            'BRL' => ['simbolo' => 'R$',   'pais' => 'Brasil'],
            'GBP' => ['simbolo' => '£',    'pais' => 'Reino Unido'],
            'JPY' => ['simbolo' => '¥',    'pais' => 'Japón'],
            'CNY' => ['simbolo' => '¥',    'pais' => 'China'],
        ];

        // 5. Datos del plan del tenant
        $planes = [
            'gratis'       => '🌱 Gratis',
            'basico'       => '🚀 Básico',
            'profesional'  => '⭐ Profesional',
            'empresarial'  => '🏢 Empresarial',
        ];

        $coloresPlan = [
            'gratis'      => '#6b7280',
            'basico'      => '#06b6d4',
            'profesional' => '#7c3aed',
            'empresarial' => '#1e1b4b',
        ];

        $badgeColor = [
            'gratis'      => 'secondary',
            'basico'      => 'info',
            'profesional' => 'primary',
            'empresarial' => 'dark',
        ];

        return view('configuracion.index', compact(
            'usuarios', 'empresa', 'stats', 'monedas',
            'planes', 'coloresPlan', 'badgeColor'
        ));
    }

    // ── Guardar / Actualizar datos de la empresa ───────────────────────
    public function updateEmpresa(Request $request)
    {
        $validated = $request->validate([
            'nombre_tienda' => 'required|string|max:255',
            'ruc'           => 'nullable|string|max:20',
            'direccion'     => 'nullable|string|max:500',
            'telefono'      => 'nullable|string|max:20',
            'whatsapp'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'igv'           => 'required|numeric|min:0|max:100',
            'moneda'        => 'required|string|max:10',
            'simbolo_moneda'=> 'required|string|max:5',
            'terminos_garantia' => 'nullable|string|max:1000',
        ]);

        $data = $validated;

        // Subir logo si se envió uno nuevo
        if ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ]);

            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = 'storage/' . $logoPath;
        }

        $empresa = Configuracion::empresa();

        if ($empresa) {
            // Eliminar logo anterior si se reemplaza
            if ($request->hasFile('logo') && $empresa->logo) {
                $oldPath = str_replace('storage/', '', $empresa->logo);
                Storage::disk('public')->delete($oldPath);
            }
            $empresa->update($data);
        } else {
            // Crear nuevo registro
            Configuracion::create($data);
        }

        return back()->with('success', 'Datos de la empresa actualizados correctamente.');
    }

    // ── Usuarios ───────────────────────────────────────────────────────
    public function storeUsuario(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100|regex:/^[\pL\pM\s\-]+$/u',
            'email'    => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:12|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/',
            'rol'      => 'required|in:admin,vendedor,tecnico',
            'telefono' => 'nullable|string|max:20|regex:/^\+?[0-9\s-]{7,20}$/',
        ]);

        // Verificar límite de usuarios del plan
        $tenant = auth()->user()->tenant;
        if ($tenant && !$tenant->puedeAgregarUsuario()) {
            return back()->with('error', 'Has alcanzado el límite de usuarios de tu plan (' . $tenant->max_usuarios . ').');
        }

        User::create([
            'name'      => trim($validated['name']),
            'email'     => strtolower($validated['email']),
            'password'  => Hash::make($validated['password']),
            'rol'       => $validated['rol'],
            'telefono'  => $validated['telefono'] ?? null,
            'tenant_id' => auth()->user()->tenant_id,
            'activo'    => true,
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function updateUsuario(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100|regex:/^[\pL\pM\s\-]+$/u',
            'email'    => 'required|email:rfc,dns|unique:users,email,' . $usuario->id,
            'rol'      => 'required|in:admin,vendedor,tecnico',
            'telefono' => 'nullable|string|max:20|regex:/^\+?[0-9\s-]{7,20}$/',
            'comision_porcentaje' => 'nullable|numeric|min:0|max:100',
            'password' => 'nullable|string|min:12|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/',
        ]);

        $data = [
            'name'     => trim($validated['name']),
            'email'    => strtolower($validated['email']),
            'rol'      => $validated['rol'],
            'telefono' => $validated['telefono'] ?? null,
        ];

        // Solo guardar comisión si el usuario es técnico
        if ($validated['rol'] === 'tecnico') {
            $data['comision_porcentaje'] = $validated['comision_porcentaje'] ?? null;
        }

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $usuario->update($data);
        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleUsuario(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }
        $usuario->update(['activo' => !$usuario->activo]);
        $estado = $usuario->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$estado} correctamente.");
    }

    public function destroyUsuario(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }
        $usuario->delete();
        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}