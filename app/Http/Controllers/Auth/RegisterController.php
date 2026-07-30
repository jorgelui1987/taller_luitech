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
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol'      => ['required', 'in:admin,vendedor,tecnico'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        // Verificar límite de usuarios del plan
        $tenant = Auth::user()?->tenant;
        if ($tenant && !$tenant->puedeAgregarUsuario()) {
            return back()->with('error', 'Has alcanzado el límite de usuarios de tu plan (' . $tenant->max_usuarios . ').');
        }

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'rol'       => $validated['rol'],
            'telefono'  => $validated['telefono'] ?? null,
            'tenant_id' => Auth::user()->tenant_id ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
