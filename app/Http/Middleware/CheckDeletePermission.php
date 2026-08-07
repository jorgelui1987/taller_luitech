<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDeletePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Solo admin y superadmin pueden eliminar registros
        if (!$user || (!$user->esAdmin() && !$user->esSuperAdmin())) {
            abort(403, 'Acceso denegado. Solo administradores pueden eliminar registros.');
        }

        return $next($request);
    }
}
