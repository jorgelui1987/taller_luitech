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

        // Prevenir clickjacking: SAMEORIGIN (no DENY) para permitir el iframe
        // oculto de impresión automática del ticket (misma origen).
        // DENY bloqueaba el iframe y por eso salía "¡Pago aprobado!" pero
        // nunca abría el diálogo de impresión.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir sniffing de MIME types
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (CSP) básico
        // frame-src permite Google Maps embebido en la página pública /t/{slug}.
        // frame-src 'self' permite el iframe oculto de impresión automática del ticket.
        // El resto sigue bloqueado (object-src 'none', sin frames externos).
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' https://api.qrserver.com https://*.googleapis.com https://*.gstatic.com https://*.google.com data:; connect-src 'self'; frame-src 'self' https://www.google.com https://maps.google.com; object-src 'none'");

        // Permissions Policy (limitar APIs del navegador)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        return $response;
    }
}
