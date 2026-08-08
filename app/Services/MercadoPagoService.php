<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de integración con Mercado Pago.
 * Permite generar QR de pago y consultar pagos.
 */
class MercadoPagoService
{
    /**
     * Verifica si la empresa tiene activado Mercado Pago.
     */
    public static function estaActivo(): bool
    {
        $config = Configuracion::empresa();
        return $config && $config->mercadopago_activo;
    }

    /**
     * Crea una preferencia de pago y genera el QR.
     *
     * @param Venta $venta
     * @return array
     */
    public function crearPago(Venta $venta): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_activo) {
            return [
                'estado'  => 'desactivado',
                'mensaje' => 'Mercado Pago desactivado para esta empresa.',
            ];
        }

        if (!$config->mercadopago_access_token) {
            throw new \Exception('Falta el Access Token de Mercado Pago. Configúralo en Configuración → Empresa.');
        }

        $moneda = $config->moneda ?? 'CLP';

        // Crear preferencia de pago
        $response = Http::withToken($config->mercadopago_access_token)
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [[
                    'title'       => "Venta {$venta->numero_venta}",
                    'quantity'    => 1,
                    'unit_price'  => (float) $venta->total,
                    'currency_id' => $moneda,
                ]],
                'external_reference' => $venta->numero_venta,
                'notification_url'   => url('/webhooks/mercadopago'),
                'back_urls' => [
                    'success' => route('ventas.show', $venta),
                    'pending' => route('ventas.show', $venta),
                    'failure' => route('ventas.show', $venta),
                ],
                'auto_return' => 'approved',
            ]);

        if (!$response->successful()) {
            Log::error('Error Mercado Pago al crear preferencia: ' . $response->body());
            throw new \Exception('Error al crear el pago en Mercado Pago.');
        }

        $data = $response->json();

        return [
            'estado'      => 'pendiente',
            'init_point'  => $data['init_point'] ?? null,
            'qr_code'     => $data['qr_code'] ?? null,
            'preference_id' => $data['id'] ?? null,
        ];
    }

    /**
     * Consulta el estado de un pago en Mercado Pago.
     *
     * @param string $paymentId
     * @return array
     */
    public function consultarPago(string $paymentId): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_access_token) {
            return ['estado' => 'error', 'mensaje' => 'Mercado Pago no configurado.'];
        }

        $response = Http::withToken($config->mercadopago_access_token)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if (!$response->successful()) {
            Log::error('Error Mercado Pago al consultar pago: ' . $response->body());
            return ['estado' => 'error', 'mensaje' => 'Error al consultar el pago.'];
        }

        $data = $response->json();

        return [
            'estado'  => $data['status'] ?? 'unknown',
            'detalle' => $data['status_detail'] ?? null,
            'monto'   => $data['transaction_amount'] ?? null,
            'referencia' => $data['external_reference'] ?? null,
        ];
    }
}