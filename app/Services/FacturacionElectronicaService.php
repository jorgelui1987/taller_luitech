<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Venta;
use App\Helpers\PaisHelper;
use Illuminate\Support\Facades\Log;

/**
 * Servicio central de facturación electrónica.
 * Despacha según el país de la empresa y verifica si la facturación está activa.
 */
class FacturacionElectronicaService
{
    /**
     * Emite la facturación electrónica para una venta.
     * Solo actúa si la empresa tiene activada la facturación electrónica.
     *
     * @param Venta $venta
     * @return array
     */
    public function emitir(Venta $venta): array
    {
        $config = Configuracion::empresa();

        // 🔴 CLAVE: Si la empresa NO tiene activada la facturación, no hace nada
        if (!$config || !$config->facturacion_electronica_activa) {
            return [
                'estado'  => 'desactivada',
                'mensaje' => 'Facturación electrónica desactivada para esta empresa.',
            ];
        }

        // Si está activada, despacha según el país
        $pais = PaisHelper::configuracionActual()['pais'];

        try {
            return match ($pais) {
                'CL' => (new FacturacionChileService())->emitir($venta),
                'CO' => (new FacturacionColombiaService())->emitir($venta),
                default => [
                    'estado'  => 'no_soportado',
                    'mensaje' => "País sin facturación electrónica configurada: {$pais}",
                ],
            };
        } catch (\Exception $e) {
            Log::error("Error en facturación electrónica (venta #{$venta->id}): " . $e->getMessage());

            return [
                'estado'  => 'error',
                'mensaje' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifica si la empresa actual tiene activada la facturación electrónica.
     */
    public static function estaActiva(): bool
    {
        $config = Configuracion::empresa();
        return $config && $config->facturacion_electronica_activa;
    }
}
