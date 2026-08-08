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
     * Webhook de Mercado Pago - recibe notificaciones de pago.
     */
    public function webhook(Request $request)
    {
        Log::info('Webhook Mercado Pago recibido', $request->all());

        $tipo = $request->input('type');
        $paymentId = $request->input('data.id');

        if ($tipo === 'payment' && $paymentId) {
            try {
                $pago = app(MercadoPagoService::class)->consultarPago($paymentId);

                if ($pago['estado'] === 'approved' && $pago['referencia']) {
                    // Buscar la venta por número de referencia
                    $venta = Venta::where('numero_venta', $pago['referencia'])->first();

                    if ($venta) {
                        $venta->update([
                            'estado_pago' => 'pagado',
                            'metodo_pago' => 'mercadopago',
                        ]);
                        Log::info("Venta {$venta->numero_venta} marcada como pagada vía Mercado Pago.");
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error procesando webhook Mercado Pago: ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
