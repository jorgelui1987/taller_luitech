<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Herramienta de EMERGENCIA para recuperar el acceso al panel SuperAdmin
 * cuando no hay acceso a terminal (ej: Dokploy).
 *
 * SEGURIDAD:
 * - La ruta está DESACTIVADA por defecto (devuelve 404).
 * - Solo se activa definiendo la variable de entorno SUPERADMIN_RECOVERY_TOKEN.
 * - El token debe coincidir exactamente con el de la URL.
 * - Se recomienda eliminar la variable y redesplegar después de usarla.
 */
class SuperAdminRecoveryController extends Controller
{
    private function tokenValido(?string $token): bool
    {
        $esperado = trim((string) getenv('SUPERADMIN_RECOVERY_TOKEN'));

        // Desactivado si la variable no está definida o está vacía
        if ($esperado === '') {
            return false;
        }

        return hash_equals($esperado, (string) $token);
    }

    public function show(string $token)
    {
        if (!$this->tokenValido($token)) {
            // Página explicativa con estado 404: permite distinguir si el
            // código actual está desplegado (muestra instrucciones) o si el
            // servidor aún corre una versión antigua (404 genérico del server).
            return response()->view('superadmin.recuperar-desactivada', [], 404);
        }

        $diagnostico = $this->diagnosticar();

        return view('superadmin.recuperar', [
            'token'       => $token,
            'diagnostico' => $diagnostico,
        ]);
    }

    public function reset(Request $request, string $token)
    {
        if (!$this->tokenValido($token)) {
            return response()->view('superadmin.recuperar-desactivada', [], 404);
        }

        $validated = $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::where('email', $validated['email'])->first();

            $datos = [
                'name'     => 'Super Admin',
                'password' => Hash::make($validated['password']),
                'rol'      => 'superadmin',
                'activo'   => true,
                'tenant_id' => null,
                'two_factor_secret'         => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at'   => null,
            ];

            if ($user) {
                $user->update($datos);
                $accion = 'actualizado';
            } else {
                User::create(array_merge(['email' => $validated['email']], $datos));
                $accion = 'creado';
            }

            Log::warning("SuperAdminRecovery: usuario {$validated['email']} {$accion} desde IP " . $request->ip());

            return back()->with('success', "✓ SuperAdmin {$accion} correctamente. Ya puedes iniciar sesión en /superadmin/login con ese email y contraseña.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    /**
     * Diagnóstico del estado de usuarios en la base de datos.
     */
    private function diagnosticar(): array
    {
        try {
            DB::connection()->getPdo();

            $superadmins = DB::table('users')
                ->where('rol', 'superadmin')
                ->get(['id', 'email', 'activo']);

            return [
                'conexion_ok'  => true,
                'error'        => null,
                'total_users'  => DB::table('users')->count(),
                'superadmins'  => $superadmins->map(fn ($u) => [
                    'id'     => $u->id,
                    'email'  => $u->email,
                    'activo' => (bool) $u->activo,
                ])->toArray(),
            ];
        } catch (\Throwable $e) {
            return [
                'conexion_ok'  => false,
                'error'        => $e->getMessage(),
                'total_users'  => 0,
                'superadmins'  => [],
            ];
        }
    }
}