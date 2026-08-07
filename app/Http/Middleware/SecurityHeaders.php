<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Agregar headers de seguridad HTTP a todas las respuestas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS (HTTP Strict Transport Security) - forzar HTTPS en el navegador
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Prevenir clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevenir sniffing de MIME types
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (CSP) básico
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' https://api.qrserver.com data:; connect-src 'self'; frame-src 'none'; object-src 'none'");

        // Permissions Policy (limitar APIs del navegador)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        return $response;
    }
}
