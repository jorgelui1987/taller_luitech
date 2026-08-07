<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class IdentifyTenant
{
    /**
     * Middleware que identifica el tenant por subdominio y lo inyecta en la request.
     * Soporta:
     * - Subdominio: mitienda.dominio.com
     * - Header X-Tenant para desarrollo: X-Tenant: mitienda
     * - Caché de 1 hora para la consulta del tenant
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;
        $host = $request->getHost();

        // 1. Detectar por header X-Tenant (útil en desarrollo local)
        if ($request->hasHeader('X-Tenant')) {
            $subdominio = $request->header('X-Tenant');
            $tenant = $this->findTenant($subdominio);
        }

        // 2. Detectar por subdominio
        if (!$tenant) {
            $subdominio = explode('.', $host)[0] ?? null;
            $esPrincipal = in_array($subdominio, ['www', 'localhost', '127', '192', '10', '172', 'crm']) || !$subdominio;

            if (!$esPrincipal) {
                $tenant = $this->findTenant($subdominio);
            }
        }

        // 3. Si se encontró un tenant, validar estado
        if ($tenant) {
            if (!$tenant->estaActivo()) {
                abort(403, 'Esta cuenta se encuentra suspendida.');
            }

            if ($tenant->haExpirado()) {
                abort(403, 'El periodo de suscripción ha expirado.');
            }

            view()->share('currentTenant', $tenant);
            $request->merge(['tenant' => $tenant]);
        }

        return $next($request);
    }

    /**
     * Busca el tenant con caché de 1 hora.
     */
    private function findTenant(string $subdominio): ?Tenant
    {
        $cacheKey = "tenant.subdomain.{$subdominio}";

        return Cache::remember($cacheKey, 3600, function () use ($subdominio) {
            return Tenant::where('subdominio', $subdominio)->first();
        });
    }
}