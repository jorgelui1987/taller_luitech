<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVentasOReparaciones
{
    /**
     * Admin, vendedor o técnico pueden cobrar (ventas o reparaciones con MP).
     * Evita el 403 cuando un técnico cobra una reparación o un vendedor
     * cobra una venta desde el módulo contrario.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || (!method_exists($user, 'puedeVender') || (!($user->puedeVender() || $user->puedeReparar())))) {
            abort(403, 'Acceso denegado. Solo administradores, vendedores y técnicos.');
        }

        return $next($request);
    }
}
