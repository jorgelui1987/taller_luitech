<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        // Solo admins pueden crear usuarios (en producción)
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (!Auth::user()?->esAdmin()) {
            abort(403, 'Acceso denegado. Solo administradores pueden crear usuarios.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255', 'regex:/^[\pL\pM\s\-]+$/u'],
            'email'    => ['required', 'string', 'email:rfc', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:12', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['in:admin,vendedor,tecnico'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s-]{7,20}$/'],
        ]);

        $tenant = Auth::user()?->tenant;
        if ($tenant && !$tenant->puedeAgregarUsuario()) {
            return back()->with('error', 'Has alcanzado el límite de usuarios de tu plan (' . $tenant->max_usuarios . ').');
        }

        // Multi-rol: casillas de roles seleccionadas en el formulario
        $roles = array_values(array_unique($validated['roles']));

        $user = User::create([
            'name'      => trim($validated['name']),
            'email'     => strtolower($validated['email']),
            'password'  => Hash::make($validated['password']),
            'rol'       => $roles[0],
            'roles'     => $roles,
            'telefono'  => $validated['telefono'] ?? null,
            'tenant_id' => Auth::user()->tenant_id ?? null,
            'activo'    => true,
        ]);

        // Un admin logueado crea usuarios para su empresa; no se cambia su sesión
        return redirect()->route('register')->with('success', "Usuario {$user->email} creado correctamente.");
    }
}
