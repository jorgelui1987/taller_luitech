<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->esSuperAdmin()) {
            abort(403, 'Acceso denegado. Solo superadministradores.');
        }

        return $next($request);
    }
}
