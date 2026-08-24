<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Renderiza la excepción.
     *
     * Diagnóstico: en errores 500 con sesión iniciada (personal de la tienda),
     * se agrega el mensaje técnico al final de la página para poder identificar
     * y corregir la causa sin necesidad de revisar los logs del servidor.
     * Los visitantes no autenticados siguen viendo la página genérica.
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        try {
            if (method_exists($response, 'status')
                && $response->status() >= 500
                && auth()->check()
                && method_exists($response, 'getContent')
                && str_contains((string) $response->getContent(), '</body>')) {

                $mensaje   = $e->getMessage() ?: get_class($e);
                $ubicacion = basename($e->getFile()) . ' línea ' . $e->getLine();

                $detalle = '<div style="padding:14px;font-family:Consolas,monospace;font-size:12px;'
                    . 'background:#fef2f2;color:#991b1b;border-top:2px solid #dc2626;word-break:break-all;text-align:left;">'
                    . '<strong>Detalle técnico (uso interno):</strong><br>'
                    . e($mensaje) . '<br><small>' . e($ubicacion) . '</small></div></body>';

                $response->setContent(str_replace('</body>', $detalle, $response->getContent()));
            }
        } catch (\Throwable) {
            // Nunca interrumpir el manejo de errores por un fallo del diagnóstico
        }

        return $response;
    }
}
