<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Reparacion;
use App\Models\Venta;
use App\Exceptions\FacturacionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Servicio de integración con Mercado Pago.
 * Permite generar QR de pago y cobrar con Point (terminal físico).
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
            throw new FacturacionException('Falta el Access Token de Mercado Pago. Configúralo en Configuración → Empresa.');
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
            throw new FacturacionException('Error al crear el pago en Mercado Pago.');
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
     * Crea una order de pago para cobrar con Point (terminal físico).
     * Usa la nueva Orders API de Mercado Pago.
     *
     * @param Venta $venta
     * @return array
     */
    public function crearPagoPoint(Venta $venta): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_activo) {
            return [
                'estado'  => 'desactivado',
                'mensaje' => 'Mercado Pago desactivado para esta empresa.',
            ];
        }

        if (!$config->mercadopago_access_token) {
            throw new FacturacionException('Falta el Access Token de Mercado Pago. Configúralo en Configuración → Empresa.');
        }

        if (!$config->mercadopago_device_id) {
            throw new FacturacionException('Falta el Device ID del Point. Configúralo en Configuración → Empresa → Mercado Pago.');
        }

        // Monto entero sin decimales (como string)
        $monto = (string) round((float) $venta->total, 0);

        // Clave estable por venta: si reintentas el mismo cobro no se encola
        // una orden nueva en el terminal (evita already_queued_order_on_terminal).
        $idempotencyKey = 'venta-' . $venta->id . '-' . $venta->numero_venta;

        // Crear order de pago para el dispositivo Point (Orders API)
        // NOTA: no se envía payment_method.default_type para que el Point
        // muestre su menú de métodos (tarjeta, débito, QR, etc.) en vez de
        // saltar directo a "cobrar con tarjeta".
        $response = Http::withToken($config->mercadopago_access_token)
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post('https://api.mercadopago.com/v1/orders', [
                'type'              => 'point',
                'external_reference' => 'VENTA-' . $venta->numero_venta,
                'expiration_time'   => 'PT16M',
                'transactions'      => [
                    'payments' => [
                        [
                            'amount' => $monto,
                        ],
                    ],
                ],
                'config'            => [
                    'point' => [
                        'terminal_id'       => $config->mercadopago_device_id,
                        // El Point imprime su comprobante de pago;
                        // el CRM imprime su propio ticket por separado.
                        'print_on_terminal' => 'seller_ticket',
                    ],
                    // Sin payment_method.default_type: el Point muestra su
                    // menú de métodos (tarjeta/débito/QR) en vez de ir
                    // directo a "cobrar con tarjeta".
                ],
                'description'       => "Venta {$venta->numero_venta}",
            ]);

        if (!$response->successful()) {
            $errorBody = $response->json();
            $mensajeError = $this->mensajeErrorPoint($response, $errorBody);
            Log::error('Error Mercado Pago Point al crear order: ' . $response->body());
            throw new FacturacionException('Error Point: ' . $mensajeError);
        }

        $data = $response->json();

        return [
            'estado'       => 'pendiente',
            'order_id'     => $data['id'] ?? null,
            'payment_id'   => $data['transactions']['payments'][0]['id'] ?? null,
            'device_id'    => $config->mercadopago_device_id,
            'mensaje'      => 'Order enviada al Point. Espera la confirmación del dispositivo.',
        ];
    }

    /**
     * Crea una preferencia de pago (QR) para una reparación.
     * Usa la referencia REP-{numero_orden} para que el webhook la identifique.
     *
     * @param Reparacion $reparacion
     * @return array
     */
    public function crearPagoReparacion(Reparacion $reparacion): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_activo) {
            return [
                'estado'  => 'desactivado',
                'mensaje' => 'Mercado Pago desactivado para esta empresa.',
            ];
        }

        if (!$config->mercadopago_access_token) {
            throw new FacturacionException('Falta el Access Token de Mercado Pago. Configúralo en Configuración → Empresa.');
        }

        $moneda = $config->moneda ?? 'CLP';
        $monto = (float) $reparacion->total;

        // Crear preferencia de pago
        $response = Http::withToken($config->mercadopago_access_token)
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [[
                    'title'       => "Reparación {$reparacion->numero_orden}",
                    'quantity'    => 1,
                    'unit_price'  => $monto,
                    'currency_id' => $moneda,
                ]],
                'external_reference' => $reparacion->numero_orden,
                'notification_url'   => url('/webhooks/mercadopago'),
                'back_urls' => [
                    'success' => route('reparaciones.show', $reparacion),
                    'pending' => route('reparaciones.show', $reparacion),
                    'failure' => route('reparaciones.show', $reparacion),
                ],
                'auto_return' => 'approved',
            ]);

        if (!$response->successful()) {
            Log::error('Error Mercado Pago al crear preferencia reparación: ' . $response->body());
            throw new FacturacionException('Error al crear el pago en Mercado Pago.');
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
     * Crea una order de pago Point para una reparación.
     *
     * @param Reparacion $reparacion
     * @return array
     */
    public function crearPagoPointReparacion(Reparacion $reparacion): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_activo) {
            return [
                'estado'  => 'desactivado',
                'mensaje' => 'Mercado Pago desactivado para esta empresa.',
            ];
        }

        if (!$config->mercadopago_access_token) {
            throw new FacturacionException('Falta el Access Token de Mercado Pago. Configúralo en Configuración → Empresa.');
        }

        if (!$config->mercadopago_device_id) {
            throw new FacturacionException('Falta el Device ID del Point. Configúralo en Configuración → Empresa → Mercado Pago.');
        }

        // Monto entero sin decimales (como string)
        $monto = (string) round((float) $reparacion->total, 0);

        // Clave estable por reparación: reintentar no encola otra orden.
        $idempotencyKey = 'rep-' . $reparacion->id . '-' . $reparacion->numero_orden;

        // Crear order de pago para el dispositivo Point (Orders API)
        // Sin payment_method.default_type para que el Point muestre su menú.
        $response = Http::withToken($config->mercadopago_access_token)
            ->withHeaders([
                'X-Idempotency-Key' => $idempotencyKey,
            ])
            ->post('https://api.mercadopago.com/v1/orders', [
                'type'              => 'point',
                'external_reference' => $reparacion->numero_orden,
                'expiration_time'   => 'PT16M',
                'transactions'      => [
                    'payments' => [
                        [
                            'amount' => $monto,
                        ],
                    ],
                ],
                'config'            => [
                    'point' => [
                        'terminal_id'       => $config->mercadopago_device_id,
                        // El Point imprime su comprobante de pago;
                        // el CRM imprime su propio ticket por separado.
                        'print_on_terminal' => 'seller_ticket',
                    ],
                    // Sin payment_method.default_type: el Point muestra su
                    // menú de métodos (tarjeta/débito/QR) en vez de ir
                    // directo a "cobrar con tarjeta".
                ],
                'description'       => "Reparación {$reparacion->numero_orden}",
            ]);

        if (!$response->successful()) {
            $errorBody = $response->json();
            $mensajeError = $this->mensajeErrorPoint($response, $errorBody);
            Log::error('Error Mercado Pago Point al crear order reparación: ' . $response->body());
            throw new FacturacionException('Error Point: ' . $mensajeError);
        }

        $data = $response->json();

        return [
            'estado'       => 'pendiente',
            'order_id'     => $data['id'] ?? null,
            'payment_id'   => $data['transactions']['payments'][0]['id'] ?? null,
            'device_id'    => $config->mercadopago_device_id,
            'mensaje'      => 'Order enviada al Point. Espera la confirmación del dispositivo.',
        ];
    }

    /**
     * Interpreta el error de la Orders API del Point y lo devuelve legible.
     * El caso típico es already_queued_order_on_terminal: el dispositivo ya
     * tiene una orden en espera (se canceló en pantalla, se quedó colgada o
     * se reintentó el cobro) y rechaza una nueva hasta liberarla.
     */
    private function mensajeErrorPoint($response, $errorBody): string
    {
        $codigos = [];
        if (is_array($errorBody)) {
            foreach ($errorBody['errors'] ?? [] as $error) {
                if (isset($error['code'])) {
                    $codigos[] = $error['code'];
                }
            }
        }
        $cuerpo = implode(' ', $codigos) . ' ' . $response->body();

        if (stripos($cuerpo, 'already_queued_order_on_terminal') !== false) {
            return 'El Point ya tiene un cobro en espera. Cancélalo en el dispositivo (o usa "Liberar terminal") y vuelve a intentar.';
        }

        if (is_array($errorBody)) {
            $msg = $errorBody['message'] ?? $errorBody['error'] ?? null;
            if (is_string($msg) && $msg !== '') {
                return $msg;
            }
            $first = $errorBody['errors'][0]['message'] ?? null;
            if (is_string($first) && $first !== '') {
                return $first;
            }
        }

        return $response->body() ?: 'Error desconocido del Point.';
    }

    /**
     * Consulta el estado de una intención de pago Point.
     *
     * @param string $paymentIntentId
     * @return array
     */
    public function consultarPagoPoint(string $paymentIntentId): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_access_token) {
            return ['estado' => 'error', 'mensaje' => 'Mercado Pago no configurado.'];
        }

        $response = Http::withToken($config->mercadopago_access_token)
            ->get("https://api.mercadopago.com/point/integration-api/payment-intents/{$paymentIntentId}");

        if (!$response->successful()) {
            Log::error('Error Mercado Pago Point al consultar intención: ' . $response->body());
            return ['estado' => 'error', 'mensaje' => 'Error al consultar el pago Point.'];
        }

        $data = $response->json();

        return [
            'estado'  => $data['state'] ?? 'unknown',
            'monto'   => $data['amount'] ?? null,
            'referencia' => $data['additional_info']['external_reference'] ?? null,
        ];
    }

    /**
     * Consulta el estado de una orden Point (Orders API v1/orders).
     *
     * Devuelve siempre un arreglo con al menos 'estado'. Cuando el pago fue
     * aprobado, 'pagado' es true y 'payment_id' trae el id del pago.
     *
     * NOTA diagnóstico (VTA-000041 se quedaba en Pendiente):
     * la respuesta real de Orders API varía según versión: a veces el pago
     * viene en transactions.payments, otras en payments, otras solo como
     * status de la orden o en payments[].transaction_details. Por eso se
     * revisan TODAS las formas conocidas + búsqueda por external_reference
     * como respaldo. Todo queda en el log (canal MP Point) para diagnosticar.
     *
     * @param string $orderId
     * @param string|null $externalReference
     * @return array
     */
    public function consultarOrdenPoint(string $orderId, ?string $externalReference = null): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_access_token) {
            return ['estado' => 'error', 'pagado' => false, 'mensaje' => 'Mercado Pago no configurado.'];
        }

        $response = Http::withToken($config->mercadopago_access_token)
            ->get("https://api.mercadopago.com/v1/orders/{$orderId}");

        if (!$response->successful()) {
            Log::error('Error Mercado Pago Point al consultar orden: ' . $response->body());
            return ['estado' => 'error', 'pagado' => false, 'mensaje' => 'Error al consultar la orden Point.'];
        }

        $data = $response->json();

        // ── Interpretar la respuesta de Orders API ─────────────────────
        $estado = strtolower((string) ($data['status'] ?? $data['order_status'] ?? $data['state'] ?? 'unknown'));
        $pagado = in_array($estado, ['processed', 'approved', 'accredited', 'closed', 'paid'], true);

        $paymentId = null;
        $candidatos = [];

        foreach ((array) ($data['transactions']['payments'] ?? []) as $pago) {
            $candidatos[] = $pago;
        }
        foreach ((array) ($data['payments'] ?? []) as $pago) {
            $candidatos[] = is_array($pago) ? $pago : ['id' => $pago, 'status' => $estado];
        }
        foreach ((array) ($data['transactions'] ?? []) as $item) {
            if (is_array($item) && (isset($item['status']) || isset($item['id']))) {
                $candidatos[] = $item;
            }
        }
        if (isset($data['payment_id'])) {
            $candidatos[] = ['id' => $data['payment_id'], 'status' => $estado];
        }

        foreach ($candidatos as $pago) {
            if (!is_array($pago)) {
                continue;
            }
            $estadoPago = strtolower((string) ($pago['status'] ?? $pago['payment_status'] ?? ''));
            if (in_array($estadoPago, ['approved', 'accredited', 'processed', 'closed', 'paid'], true)) {
                $pagado = true;
                $paymentId = $pago['id'] ?? $pago['payment_id'] ?? $paymentId;
                break;
            }
            if ($paymentId === null && isset($pago['id'])) {
                $paymentId = $pago['id'];
            }
        }

        // Respaldo: si la orden no trae el pago pero sí referencia externa,
        // buscar el pago aprobado por external_reference en la API de pagos.
        if (!$pagado && ($externalReference || isset($data['external_reference']))) {
            $ref = $externalReference ?? $data['external_reference'];
            $respaldo = $this->buscarPagoPorReferencia((string) $ref);
            if (!empty($respaldo['pagado'])) {
                $pagado = true;
                $paymentId = $respaldo['payment_id'] ?? $paymentId;
                Log::info("MP Point orden {$orderId}: pago detectado por respaldo external_reference {$ref}.");
            }
        }

        Log::info("MP Point orden {$orderId}: estado={$estado} pagado=" . ($pagado ? 'SI' : 'NO'));

        return [
            'estado'     => $estado,
            'pagado'     => $pagado,
            'payment_id' => $paymentId,
            'monto'      => $data['total_amount'] ?? $data['amount'] ?? null,
            'referencia' => $data['external_reference'] ?? $externalReference,
        ];
    }

    /**
     * Respaldo: busca un pago aprobado por external_reference.
     * Se usa cuando la orden Point no expone el pago en su JSON
     * (la venta se quedaba en Pendiente aunque el Point sí cobró).
     *
     * @param string $referencia Ej: VENTA-VTA-000041
     * @return array
     */
    public function buscarPagoPorReferencia(string $referencia): array
    {
        $config = Configuracion::empresa();

        if (!$config || !$config->mercadopago_access_token) {
            return ['pagado' => false];
        }

        $response = Http::withToken($config->mercadopago_access_token)
            ->get('https://api.mercadopago.com/v1/payments/search', [
                'external_reference' => $referencia,
                'sort' => 'date_created',
                'criteria' => 'desc',
                'range' => 'date_created',
                'begin_date' => now()->subDays(2)->toIso8601String(),
                'end_date' => now()->addDay()->toIso8601String(),
            ]);

        if (!$response->successful()) {
            Log::warning('MP Point respaldo: error buscando pago por referencia ' . $referencia . ': ' . $response->body());
            return ['pagado' => false];
        }

        foreach ((array) ($response->json()['results'] ?? []) as $pago) {
            if (strtolower((string) ($pago['status'] ?? '')) === 'approved') {
                return ['pagado' => true, 'payment_id' => $pago['id'] ?? null];
            }
        }

        return ['pagado' => false];
    }

    /**
     * Crea la venta automática de una reparación cuyo pago Point fue aprobado.
     * Es la misma venta que antes solo creaba el webhook: si ya existe (por
     * webhook o reintento), la devuelve sin duplicar.
     *
     * @param Reparacion $reparacion
     * @param string|null $paymentId
     * @return \App\Models\Venta
     */
    public function crearVentaPorReparacion(Reparacion $reparacion, ?string $paymentId = null): \App\Models\Venta
    {
        $existente = \App\Models\Venta::where('notas', 'like', "%{$reparacion->numero_orden}%")
            ->orderByDesc('id')
            ->first();

        if ($existente) {
            // Si estaba pendiente, dejarla pagada; si ya está completada, no tocar.
            if ($existente->estado !== 'completada' || $existente->estado_pago !== 'pagado') {
                $datos = ['estado' => 'completada', 'estado_pago' => 'pagado', 'metodo_pago' => 'mercadopago'];
                if ($paymentId) {
                    $datos['referencia_pago'] = $existente->referencia_pago ?: ('MP-' . $paymentId);
                }
                $existente->update($datos);
            }
            return $existente->fresh();
        }

        $totalReparacion = (float) $reparacion->total;

        // Tenant: el de la reparación; si viene vacío (ej. webhook sin sesión),
        // resolver por el tenant del técnico/cliente antes de crear, porque el
        // global scope TenantScope ocultaría la venta en Ventas/Dashboard.
        $tenantId = $reparacion->tenant_id;
        if (!$tenantId) {
            $tecnico = $reparacion->tecnico;
            $cliente = $reparacion->cliente;
            $tenantId = $tecnico?->tenant_id ?? $cliente?->tenant_id;
        }

        return \App\Models\Venta::create([
            'numero_venta'   => \App\Models\Venta::generarNumero(),
            'cliente_id'     => $reparacion->cliente_id,
            'user_id'        => $reparacion->tecnico_id ?? \Illuminate\Support\Facades\Auth::id(),
            'fecha_venta'    => now(),
            'subtotal'       => $totalReparacion,
            'descuento'      => 0,
            'impuesto'       => (float) ($reparacion->impuesto ?? 0),
            'total'          => $totalReparacion,
            'comision_monto' => (float) ($reparacion->comision_monto ?? 0),
            'comision_pagada'=> false,
            'metodo_pago'    => 'mercadopago',
            'referencia_pago'=> $paymentId ? ('MP-' . $paymentId) : null,
            'estado'         => 'completada',
            'estado_pago'    => 'pagado',
            'notas'          => "Pago por reparación {$reparacion->numero_orden} - {$reparacion->dispositivo}",
            'tenant_id'      => $tenantId,
        ]);
    }

    /**
     * Marca la venta como pagada cuando la orden Point ya fue aprobada.
     * Se usa desde el polling del navegador (funciona aunque el webhook
     * no llegue, ej. entorno local sin URL pública).
     *
     * @param Venta $venta
     * @param string|null $paymentId
     * @return void
     */
    public function confirmarVentaPoint(Venta $venta, ?string $paymentId = null): void
    {
        $datos = ['estado' => 'completada', 'estado_pago' => 'pagado', 'metodo_pago' => 'mercadopago'];
        if ($paymentId) {
            $datos['referencia_pago'] = $venta->referencia_pago ?: ('MP-' . $paymentId);
        }
        $venta->update($datos);
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