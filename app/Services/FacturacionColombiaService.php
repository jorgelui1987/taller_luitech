<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Venta;
use App\Exceptions\FacturacionException;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de facturación electrónica para Colombia (FEV - DIAN).
 * Integración con la DIAN o proveedor tecnológico autorizado.
 *
 * NOTA: Este es un stub base. Para producción, conectar con la API
 * de la DIAN o del proveedor tecnológico seleccionado.
 */
class FacturacionColombiaService
{
    /**
     * Emite una Factura Electrónica de Venta (FEV) para una venta.
     *
     * @param Venta $venta
     * @return array
     */
    public function emitir(Venta $venta): array
    {
        $config = Configuracion::empresa();

        // Validar que la empresa tenga los datos necesarios
        if (!$config->ruc) {
            throw new FacturacionException('Falta el NIT de la empresa. Configúralo en Configuración → Empresa.');
        }

        // TODO: Integración real con la DIAN
        // 1. Construir XML de la factura
        // 2. Calcular CUFE (hash SHA-384 con llave técnica)
        // 3. Enviar a la DIAN o proveedor tecnológico
        // 4. Guardar CUFE, resolución y estado

        Log::info("Facturación Colombia (stub): Venta #{$venta->id}, NIT {$config->ruc}");

        return [
            'estado'  => 'pendiente',
            'mensaje' => 'Factura electrónica pendiente de envío a la DIAN.',
        ];
    }
}
