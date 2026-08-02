<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\PlanPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    private function limitesPorPlan(): array
    {
        return [
            'gratis'      => ['max_usuarios' => 3,  'max_productos' => 50],
            'basico'      => ['max_usuarios' => 5,  'max_productos' => 200],
            'profesional' => ['max_usuarios' => 15, 'max_productos' => 1000],
            'empresarial' => ['max_usuarios' => 999, 'max_productos' => 99999],
        ];
    }

    private function eliminarDatosTenant(int $tenantId): void
    {
        $ordenIds = DB::table('ordenes_compra')->where('tenant_id', $tenantId)->pluck('id');
        if ($ordenIds->isNotEmpty()) {
            DB::table('detalle_ordenes_compra')->whereIn('orden_compra_id', $ordenIds)->delete();
        }

        DB::table('ordenes_compra')->where('tenant_id', $tenantId)->delete();

        $ventaIds = DB::table('ventas')->where('tenant_id', $tenantId)->pluck('id');
        if ($ventaIds->isNotEmpty()) {
            DB::table('detalle_ventas')->whereIn('venta_id', $ventaIds)->delete();
        }

        DB::table('ventas')->where('tenant_id', $tenantId)->delete();

        $reparacionIds = DB::table('reparaciones')->where('tenant_id', $tenantId)->pluck('id');
        if ($reparacionIds->isNotEmpty()) {
            DB::table('reparacion_fotos')->whereIn('reparacion_id', $reparacionIds)->delete();
        }

        DB::table('reparaciones')->where('tenant_id', $tenantId)->delete();

        if (DB::getSchemaBuilder()->hasColumn('reparaciones', 'deleted_at')) {
            DB::table('reparaciones')->where('tenant_id', $tenantId)->whereNotNull('deleted_at')->delete();
        }

        // Tablas con columna tenant_id definida por migración
        $tablasConTenant = [
            'movimientos_stock',
            'comisiones_pagos',
            'gastos_fijos',
            'productos',
            'proveedores',
            'clientes',
            'configuracion',
            'users',
        ];

        foreach ($tablasConTenant as $tabla) {
            if (DB::getSchemaBuilder()->hasColumn($tabla, 'tenant_id')) {
                DB::table($tabla)->where('tenant_id', $tenantId)->delete();
            }
        }

        // categorias y marcas NO tienen columna tenant_id (tablas globales),
        // por lo que solo se eliminan si efectivamente la tienen.
        foreach (['categorias', 'marcas'] as $tabla) {
            if (DB::getSchemaBuilder()->hasColumn($tabla, 'tenant_id')) {
                DB::table($tabla)->where('tenant_id', $tenantId)->delete();
            }
        }
    }

    // ─── Autenticación SuperAdmin ────────────────────────────────────────
    public function showLoginForm()
    {
        return view('superadmin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Solo superadmins pueden acceder aquí
        if (Auth::attempt($credentials) && Auth::user()->rol === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        Auth::logout();
        return back()->with('error', 'Credenciales inválidas o no tienes permisos de superadmin.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('superadmin.login');
    }

    // ─── Dashboard SuperAdmin ────────────────────────────────────────────
    public function dashboard()
    {
        $stats = [
            'total_tenants'      => Tenant::count(),
            'tenants_activos'    => Tenant::where('estado', 'activo')->count(),
            'tenants_suspendidos' => Tenant::where('estado', 'suspendido')->count(),
            'usuarios_totales'   => User::where('rol', '!=', 'superadmin')->count(),
            'ultimos_tenants'    => Tenant::latest()->take(5)->get(),
        ];

        return view('superadmin.dashboard', compact('stats'));
    }

    // ─── Gestión de Tenants ──────────────────────────────────────────────
    public function tenants()
    {
        $tenants = Tenant::withCount('usuarios')->orderByDesc('created_at')->paginate(15);
        return view('superadmin.tenants', compact('tenants'));
    }

    public function createTenant()
    {
        $limitesPorPlan = $this->limitesPorPlan();
        return view('superadmin.tenant-form', compact('limitesPorPlan'));
    }

    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'empresa'          => 'required|string|max:255|unique:tenants,empresa',
            'subdominio'       => 'required|string|max:50|unique:tenants,subdominio|regex:/^[a-z0-9-]+$/',
            'email_contacto'   => 'required|email|max:255',
            'telefono_contacto'=> 'nullable|string|max:20',
            'plan'             => 'required|in:gratis,basico,profesional,empresarial',
            'max_usuarios'     => 'required|integer|min:1',
            'max_productos'    => 'required|integer|min:1',
            'fecha_expiracion' => 'nullable|date',
            'nombre_admin'     => 'required|string|max:100',
            'email_admin'      => 'required|email|max:255|unique:users,email',
            'password_admin'   => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Crear el tenant
            $tenant = Tenant::create([
                'empresa'          => $validated['empresa'],
                'subdominio'       => $validated['subdominio'],
                'email_contacto'   => $validated['email_contacto'],
                'telefono_contacto'=> $validated['telefono_contacto'],
                'plan'             => $validated['plan'],
                'estado'           => 'activo',
                'max_usuarios'     => $validated['max_usuarios'],
                'max_productos'    => $validated['max_productos'],
                'fecha_expiracion' => $validated['fecha_expiracion'],
            ]);

            // 2. Crear el usuario admin del tenant
            User::create([
                'name'      => $validated['nombre_admin'],
                'email'     => $validated['email_admin'],
                'password'  => Hash::make($validated['password_admin']),
                'rol'       => 'admin',
                'activo'    => true,
                'tenant_id' => $tenant->id,
            ]);

            // 3. Crear configuración inicial para el tenant
            Configuracion::create([
                'nombre_tienda'    => $validated['empresa'],
                'igv'              => 18.00,
                'moneda'           => 'PEN',
                'simbolo_moneda'   => 'S/',
                'tenant_id'        => $tenant->id,
            ]);
        });

        return redirect()->route('superadmin.tenants')
            ->with('success', "Tenant '{$validated['empresa']}' creado correctamente.");
    }

    public function editTenant($id)
    {
        // withoutGlobalScopes para que el superadmin pueda ver TODOS los tenants
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $limitesPorPlan = [
            'gratis'       => ['max_usuarios' => 3,  'max_productos' => 50],
            'basico'       => ['max_usuarios' => 5,  'max_productos' => 200],
            'profesional'  => ['max_usuarios' => 15, 'max_productos' => 1000],
            'empresarial'  => ['max_usuarios' => 999,'max_productos' => 99999],
        ];
        return view('superadmin.tenant-form', compact('tenant', 'limitesPorPlan'));
    }

    public function updateTenant(Request $request, $id)
    {
        // withoutGlobalScopes para que el superadmin pueda ver TODOS los tenants
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $validated = $request->validate([
            'empresa'          => "required|string|max:255|unique:tenants,empresa,{$tenant->id}",
            'subdominio'       => "required|string|max:50|unique:tenants,subdominio,{$tenant->id}|regex:/^[a-z0-9-]+$/",
            'email_contacto'   => 'required|email|max:255',
            'telefono_contacto'=> 'nullable|string|max:20',
            'plan'             => 'required|in:gratis,basico,profesional,empresarial',
            'fecha_expiracion' => 'nullable|date',
            'estado'           => 'required|in:activo,suspendido,cancelado',
        ]);

        $limitesPorPlan = $this->limitesPorPlan();
        $limites = $limitesPorPlan[$validated['plan']] ?? $limitesPorPlan['gratis'];

        $validated['max_usuarios']  = $limites['max_usuarios'];
        $validated['max_productos'] = $limites['max_productos'];

        $tenant->update($validated);

        return redirect()->route('superadmin.tenants')
            ->with('success', "Tenant '{$tenant->empresa}' actualizado a plan {$validated['plan']}.");
    }

    public function toggleTenant($id)
    {
        // withoutGlobalScopes para que el superadmin pueda ver TODOS los tenants
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $nuevoEstado = $tenant->estado === 'activo' ? 'suspendido' : 'activo';
        $tenant->update(['estado' => $nuevoEstado]);

        return back()->with('success', "Tenant '{$tenant->empresa}' {$nuevoEstado}.");
    }

    public function destroyTenant($id)
    {
        // withoutGlobalScopes para que el superadmin pueda ver TODOS los tenants
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $nombre = $tenant->empresa;
        $tenantId = $tenant->id;

        // Eliminar en orden correcto para respetar foreign keys
        // (No se desactivan restricciones porque el orden de eliminación es correcto)
        DB::transaction(function () use ($tenantId) {
            $this->eliminarDatosTenant($tenantId);
            DB::table('tenants')->where('id', $tenantId)->delete();
        });

        return redirect()->route('superadmin.tenants')
            ->with('success', "Tenant '{$nombre}' eliminado permanentemente.");
    }

    /**
     * Muestra los usuarios de un tenant (para soporte técnico).
     */
    public function tenantUsers($id)
    {
        // withoutGlobalScopes para que el superadmin pueda ver TODOS los tenants
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $usuarios = User::where('tenant_id', $tenant->id)->orderBy('rol')->orderBy('name')->get();
        return view('superadmin.tenant-users', compact('tenant', 'usuarios'));
    }

    /**
     * Cambia la contraseña de cualquier usuario (para recuperación).
     */
    public function changeUserPassword(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $usuario->password = Hash::make($validated['password']);
        $usuario->save();

        return back()->with('success', "Contraseña actualizada para {$usuario->name}.");
    }

    /**
     * Login como un tenant específico (para soporte técnico).
     */
    public function loginAsTenant($id)
    {
        // withoutGlobalScopes para que el superadmin pueda ver TODOS los tenants
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $admin = User::where('tenant_id', $tenant->id)
            ->where('rol', 'admin')
            ->first();

        if (!$admin) {
            return back()->with('error', 'Este tenant no tiene un usuario admin.');
        }

        Auth::login($admin);
        return redirect()->route('dashboard')
            ->with('success', "Has iniciado sesión como admin de {$tenant->empresa}");
    }

    // ─── Registro público de tenant (landing page) ──────────────────────
    public function showRegistroTenant()
    {
        return view('registro-tenant');
    }

    public function registrarTenant(Request $request)
    {
        $validated = $request->validate([
            'empresa'        => 'required|string|max:255|unique:tenants,empresa',
            'nombre'         => 'required|string|max:100',
            'email'          => 'required|email|max:255|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'terminos'       => 'accepted',
        ]);

        // Generar subdominio automáticamente a partir del nombre de la empresa
        $subdominio = Str::slug($validated['empresa'], '-');
        $subdominio = preg_replace('/[^a-z0-9-]/', '', strtolower($subdominio));
        $subdominio = substr($subdominio, 0, 50);

        // Asegurar que sea único
        $original = $subdominio;
        $counter = 1;
        while (Tenant::where('subdominio', $subdominio)->exists()) {
            $subdominio = $original . '-' . $counter;
            $counter++;
        }

        DB::transaction(function () use ($validated, $subdominio) {
            $tenant = Tenant::create([
                'empresa'          => $validated['empresa'],
                'subdominio'       => $subdominio,
                'email_contacto'   => $validated['email'],
                'plan'             => 'gratis',
                'estado'           => 'activo',
                'max_usuarios'     => 3,
                'max_productos'    => 50,
            ]);

            User::create([
                'name'      => $validated['nombre'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'rol'       => 'admin',
                'activo'    => true,
                'tenant_id' => $tenant->id,
            ]);

            Configuracion::create([
                'nombre_tienda'  => $validated['empresa'],
                'igv'            => 18.00,
                'moneda'         => 'PEN',
                'simbolo_moneda' => 'S/',
                'tenant_id'      => $tenant->id,
            ]);
        });

        return redirect()->route('login')
            ->with('success', '¡Cuenta creada exitosamente! Inicia sesión con tu correo y contraseña.');
    }

    // ─── Gestión de Precios de Planes ───────────────────────────────────
    public function planPreciosIndex()
    {
        try {
            $planes = PlanPrecio::orderBy('precio_mensual')->get();
        } catch (\Exception $e) {
            $planes = collect([]);
        }
        return view('superadmin.planes-precios', compact('planes'));
    }

    public function planPreciosUpdate(Request $request, PlanPrecio $planPrecio)
    {
        $validated = $request->validate([
            'precio_mensual' => 'required|numeric|min:0',
            'moneda'         => 'required|string|max:10',
            'simbolo'        => 'required|string|max:10',
            'descripcion'    => 'nullable|string|max:255',
            'activo'         => 'boolean',
        ]);

        $validated['activo'] = $request->has('activo');

        $planPrecio->update($validated);

        return redirect()->route('superadmin.planes-precios')
            ->with('success', "Precio del plan {$planPrecio->nombre} actualizado correctamente.");
    }
}
