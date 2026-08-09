<?php

namespace App\Http\Controllers;

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

        // Enviar la respuesta y continuar el procesamiento en segundo plano
        if (function_exists('fastcgi_finish_request')) {
            $response->send();
            fastcgi_finish_request();
        }

        // ── Procesar la notificación en segundo plano ──
        try {
            // Caso 1: Notificación de pago (type=payment)
            if ($tipo === 'payment' && isset($data['id'])) {
                $pago = app(MercadoPagoService::class)->consultarPago($data['id']);

                if ($pago['estado'] === 'approved' && $pago['referencia']) {
                    $venta = Venta::where('numero_venta', $pago['referencia'])->first();

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

            // Caso 2: Notificación de order Point (type=order)
            if ($tipo === 'order' && $action === 'order.processed' && isset($data['external_reference'])) {
                $referencia = $data['external_reference'];
                $status = $data['status'] ?? '';

                // Buscar la venta por referencia (VENTA-VTA-000XXX)
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
        } catch (\Exception $e) {
            Log::error('Error procesando webhook Mercado Pago: ' . $e->getMessage());
        }

        return $response;
    }
}
