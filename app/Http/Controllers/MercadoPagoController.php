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
     * Responde 200 inmediatamente a Mercado Pago y procesa la notificación
     * en segundo plano (fastcgi_finish_request) para evitar timeouts que
     * provocan errores 502 Bad Gateway al llamar a la API de Mercado Pago
     * de forma síncrona.
     */
    public function webhook(Request $request)
    {
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

                    // Si la referencia es de una REPARACIÓN (REP-...), crear la venta automática
                    if (str_starts_with($referencia, 'REP-')) {
                        $reparacion = Reparacion::where('numero_orden', $referencia)->first();
                        if ($reparacion) {
                            // Crear la venta automática de la reparación (Opción B)
                            $totalReparacion = (float) $reparacion->total;
                            $comisionMonto = (float) $reparacion->comision_monto ?? 0;

                            // Usar valores seguros (evitar null en user_id/tenant_id)
                            $userId = $reparacion->tecnico_id ?? \App\Models\User::where('rol', 'admin')->value('id');
                            $tenantId = $reparacion->tenant_id ?? \App\Models\Configuracion::empresa()?->tenant_id;

                            Venta::create([
                                'numero_venta'   => Venta::generarNumero(),
                                'cliente_id'     => $reparacion->cliente_id,
                                'user_id'        => $userId,
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
                                'tenant_id'      => $tenantId,
                            ]);
                            Log::info("Venta creada por reparación {$reparacion->numero_orden} vía Mercado Pago.");
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
}
