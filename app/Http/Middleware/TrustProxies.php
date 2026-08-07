<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Confiar en todos los proxies (Dokploy, Traefik, Nginx, etc.)
     * '*' confía en cualquier proxy que esté por delante de la aplicación.
     */
    protected $proxies = '*';

    /**
     * Cabeceras que se deben usar para detectar el protocolo original (HTTPS).
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
