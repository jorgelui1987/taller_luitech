<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    /**
     * Middleware que verifica el estado del tenant en cada request autenticado.
     * Si el tenant está suspendido o expirado, cierra la sesión y redirige al login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Saltar verificación para superadmins
            if ($user->esSuperAdmin()) {
                return $next($request);
            }

            $tenant = $user->tenant;

            if ($tenant) {
                // Verificar si está suspendido
                if ($tenant->estaSuspendido()) {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')
                        ->with('error', 'Tu cuenta ha sido suspendida. Contacta al administrador.');
                }

                // Verificar si ha expirado
                if ($tenant->haExpirado()) {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')
                        ->with('error', 'Tu suscripción ha expirado. Contacta al administrador para renovar.');
                }

                // Verificar si está cancelado
                if ($tenant->estado === 'cancelado') {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')
                        ->with('error', 'Tu cuenta ha sido cancelada.');
                }
            }
        }

        return $next($request);
    }
}