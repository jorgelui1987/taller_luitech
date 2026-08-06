<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReparacionesAccess
{
    /**
     * Solo admin y técnico pueden acceder a reparaciones.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->rol, ['admin', 'tecnico'])) {
            abort(403, 'Acceso denegado. Solo administradores y técnicos.');
        }

        return $next($request);
    }
}