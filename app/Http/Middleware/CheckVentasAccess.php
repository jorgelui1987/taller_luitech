<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVentasAccess
{
    /**
     * Solo admin y vendedor pueden acceder a ventas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->puedeVender()) {
            abort(403, 'Acceso denegado. Solo administradores y vendedores.');
        }

        return $next($request);
    }
}
