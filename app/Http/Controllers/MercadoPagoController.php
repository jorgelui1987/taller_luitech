<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\Venta;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    /**
     * Genera el QR de pago para una venta.
     */
    public function generarPago(Venta $venta)
    {
        try {
            $pago = app(MercadoPagoService::class)->crearPago($venta);

            if ($pago['estado'] === 'desactivado') {
                return back()->with('error', 'Mercado Pago no está activado para esta empresa.');
            }

            return back()->with('mercadopago', $pago);
        } catch (\Exception $e) {
            Log::error('Error generando pago Mercado Pago: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Envía el cobro al terminal Point (dispositivo físico).
     */
    public function cobrarPoint(Venta $venta)
    {
        try {
            $pago = app(MercadoPagoService::class)->crearPagoPoint($venta);

            if ($pago['estado'] === 'desactivado') {
                return back()->with('error', 'Mercado Pago no está activado para esta empresa.');
            }

            return back()->with('point', $pago);
        } catch (\Exception $e) {
            Log::error('Error cobrando con Point: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Genera el QR de pago para una reparación.
     */
    public function generarPagoReparacion(Reparacion $reparacion)
    {
        try {
            $pago = app(MercadoPagoService::class)->crearPagoReparacion($reparacion);

            if ($pago['estado'] === 'desactivado') {
                return back()->with('error', 'Mercado Pago no está activado para esta empresa.');
            }

            return back()->with('mercadopago_reparacion', $pago);
        } catch (\Exception $e) {
            Log::error('Error generando pago reparacion Mercado Pago: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Envía el cobro al terminal Point para una reparación.
     */
    public function cobrarPointReparacion(Reparacion $reparacion)
    {
        try {
            $pago = app(MercadoPagoService::class)->crearPagoPointReparacion($reparacion);

            if ($pago['estado'] === 'desactivado') {
                return back()->with('error', 'Mercado Pago no está activado para esta empresa.');
            }

            return back()->with('point_reparacion', $pago);
        } catch (\Exception $e) {
            Log::error('Error cobrando reparación con Point: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Webhook de Mercado Pago - recibe notificaciones de pago.
     * Soporta tanto type=payment como type=order (Point).
     *
     * La URL se excluye de la verificación CSRF porque Mercado Pago no envía
     * tokens CSRF. La seguridad se garantiza validando la firma HMAC-SHA256
     * que Mercado Pago incluye en las cabeceras X-Signature y x-request-id.
     */
    public function webhook(Request $request)
    {
        // ── Validar firma de Mercado Pago ──────────────────────────────
        if (!$this->validarFirmaWebhook($request)) {
            Log::warning('Webhook Mercado Pago rechazado: firma inválida', $request->headers->all());
            return response()->json(['error' => 'Firma inválida'], 401);
        }

        Log::info('Webhook Mercado Pago recibido', $request->all());

        $tipo = $request->input('type');
        $action = $request->input('action');
        $data = $request->input('data', []);

        // Responder 200 inmediatamente a Mercado Pago
        $response = response()->json(['status' => 'ok']);

        // ── Procesar la notificación de forma SÍNCRONA ──
        // (NO usar fastcgi_finish_request porque corta la conexión a la BD
        // y la notificación nunca se procesa para crear la venta de reparación)
        try {
            // Caso 1: Notificación de pago (type=payment)
            if ($tipo === 'payment' && isset($data['id'])) {
                $pago = app(MercadoPagoService::class)->consultarPago($data['id']);

                if ($pago['estado'] === 'approved' && $pago['referencia']) {
                    $referencia = $pago['referencia'];

                    // Si la referencia es de una REPARACIÓN (REP-...), completar la venta pendiente
                    if (str_starts_with($referencia, 'REP-')) {
                        $ventaPendiente = Venta::where('notas', 'like', "%{$referencia}%")
                            ->where('estado', 'pendiente')
                            ->orderByDesc('id')
                            ->first();

                        if ($ventaPendiente) {
                            $ventaPendiente->update([
                                'estado'      => 'completada',
                                'estado_pago' => 'pagado',
                                'metodo_pago' => 'mercadopago',
                            ]);
                            Log::info("Venta {$ventaPendiente->numero_venta} (reparación {$referencia}) completada vía Mercado Pago.");
                        }
                    } else {
                        // Es una venta normal
                        $venta = Venta::where('numero_venta', $referencia)->first();

                        if ($venta) {
                            $venta->update([
                                'estado'      => 'completada',
                                'estado_pago' => 'pagado',
                                'metodo_pago' => 'mercadopago',
                            ]);
                            Log::info("Venta {$venta->numero_venta} marcada como pagada vía Mercado Pago.");
                        }
                    }
                }
            }

            // Caso 2: Notificación de order Point (type=order)
            if ($tipo === 'order' && $action === 'order.processed' && isset($data['external_reference'])) {
                $referencia = $data['external_reference'];
                $status = $data['status'] ?? '';

                // Si la referencia es de una REPARACIÓN (REP-...), crear la venta automática
                if (str_starts_with($referencia, 'REP-')) {
                    $reparacion = Reparacion::where('numero_orden', $referencia)->first();
                    if ($reparacion && $status === 'processed') {
                        $totalReparacion = (float) $reparacion->total;
                        $comisionMonto = (float) $reparacion->comision_monto ?? 0;

                        Venta::create([
                            'numero_venta'   => Venta::generarNumero(),
                            'cliente_id'     => $reparacion->cliente_id,
                            'user_id'        => $reparacion->tecnico_id,
                            'fecha_venta'    => now(),
                            'subtotal'       => $totalReparacion,
                            'descuento'      => 0,
                            'impuesto'       => 0,
                            'total'          => $totalReparacion,
                            'comision_monto' => $comisionMonto,
                            'comision_pagada'=> false,
                            'metodo_pago'    => 'mercadopago',
                            'estado'         => 'completada',
                            'estado_pago'    => 'pagado',
                            'notas'          => "Pago por reparación {$reparacion->numero_orden} - {$reparacion->dispositivo}",
                            'tenant_id'      => $reparacion->tenant_id,
                        ]);
                        Log::info("Venta creada por reparación {$reparacion->numero_orden} vía Point.");
                    }
                } else {
                    // Es una venta normal
                    $numeroVenta = str_replace('VENTA-', '', $referencia);
                    $venta = Venta::where('numero_venta', $numeroVenta)->first();

                    if ($venta && $status === 'processed') {
                        $venta->update([
                            'estado'      => 'completada',
                            'estado_pago' => 'pagado',
                            'metodo_pago' => 'mercadopago',
                        ]);
                        Log::info("Venta {$venta->numero_venta} marcada como pagada vía Point.");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error procesando webhook Mercado Pago: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Valida la firma HMAC-SHA256 de la notificación de Mercado Pago.
     *
     * IMPORTANTE: Si NO hay webhook secret configurado, se permite el webhook
     * sin validación (por compatibilidad con instalaciones anteriores).
     * Si SÍ hay secret configurado (en Configuración → Empresa), la firma
     * se valida estrictamente con HMAC-SHA256.
     *
     * @param Request $request
     * @return bool
     */
    private function validarFirmaWebhook(Request $request): bool
    {
        $config = \App\Models\Configuracion::empresa();
        $secret = $config->mercadopago_webhook_secret ?? null;

        // ── Si NO hay secret configurado: permitir el webhook ─────────────
        // Esto mantiene compatibilidad con instalaciones que no han configurado
        // el webhook secret. La desactivación del CSRF sigue siendo necesaria
        // porque Mercado Pago no envía tokens CSRF.
        if (!$secret) {
            Log::info('Webhook Mercado Pago: sin webhook secret configurado, se permite (compatibilidad).');
            return true;
        }

        $signature = $request->header('x-signature');
        $requestId = $request->header('x-request-id');

        if (!$signature || !$requestId) {
            Log::warning('Webhook Mercado Pago: faltan cabeceras de firma.');
            return false;
        }

        // Extraer la firma ts=v1:hash
        // Formato: ts=<timestamp>,v1=<hash>
        $parts = explode(',', $signature);
        $ts = null;
        $hash = null;

        foreach ($parts as $part) {
            if (str_starts_with($part, 'ts=')) {
                $ts = substr($part, 3);
            } elseif (str_starts_with($part, 'v1=')) {
                $hash = substr($part, 3);
            }
        }

        if (!$ts || !$hash) {
            return false;
        }

        // Construir la cadena a firmar: id:<requestId>;request-id:<requestId>;ts:<ts>;
        $manifest = "id:{$requestId};request-id:{$requestId};ts:{$ts};";

        $expectedHash = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expectedHash, $hash);
    }
}
