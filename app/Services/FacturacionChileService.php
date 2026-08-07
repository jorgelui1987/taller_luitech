<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Venta;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de facturación electrónica para Chile (DTE - SII).
 * Integración con proveedores DTE: Acepta, Fove, Tundra.
 *
 * NOTA: Este es un stub base. Para producción, conectar con la API
 * del proveedor DTE seleccionado en la configuración.
 */
class FacturacionChileService
{
    /**
     * Emite un DTE (Factura 33 o Boleta 39) para una venta.
     *
     * @param Venta $venta
     * @return array
     */
    public function emitir(Venta $venta): array
    {
        $config = Configuracion::empresa();

        // Validar que la empresa tenga los datos necesarios
        if (!$config->rut_emisor) {
            throw new \Exception('Falta el RUT del emisor. Configúralo en Configuración → Empresa.');
        }

        if (!$config->proveedor_dte) {
            throw new \Exception('Falta seleccionar el proveedor DTE (Acepta, Fove o Tundra).');
        }

        // Determinar tipo de documento: 33 = Factura, 39 = Boleta
        $tipoDte = $venta->cliente_id ? '33' : '39';

        // TODO: Integración real con el proveedor DTE
        // 1. Construir XML del DTE
        // 2. Firmar con certificado digital
        // 3. Enviar al proveedor (Acepta/Fove/Tundra)
        // 4. Guardar folio, token, PDF y estado

        Log::info("Facturación Chile (stub): Venta #{$venta->id}, tipo DTE {$tipoDte}, proveedor {$config->proveedor_dte}");

        // Guardar el tipo de DTE en la venta
        $venta->update([
            'dte_tipo'   => $tipoDte,
            'dte_estado' => 'pendiente',
        ]);

        return [
            'estado'  => 'pendiente',
            'tipo'    => $tipoDte,
            'mensaje' => 'DTE generado y pendiente de envío al SII.',
        ];
    }
}