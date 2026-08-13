<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * El webhook de Mercado Pago se excluye de CSRF porque Mercado Pago
     * no envía tokens CSRF en sus notificaciones. La seguridad se garantiza
     * mediante la validación de la firma HMAC-SHA256 en el controlador
     * (MercadoPagoController::webhook), que verifica que la notificación
     * proviene realmente de Mercado Pago.
     *
     * @var array<int, string>
     */
    protected $except = [
        'webhooks/mercadopago',
    ];
}
