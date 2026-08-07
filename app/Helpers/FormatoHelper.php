<?php

namespace App\Helpers;

class FormatoHelper
{
    /**
     * Formatea un monto CON 2 decimales (para facturas, ventas, tickets).
     */
    public static function monto($monto): string
    {
        return number_format((float)$monto, 2);
    }

    /**
     * Formatea un monto SIN decimales (para dashboard, tarjetas, gráficos).
     */
    public static function montoEntero($monto): string
    {
        return number_format((float)$monto, 0);
    }

    /**
     * Formatea un monto con símbolo de moneda y 2 decimales.
     */
    public static function moneda($monto): string
    {
        $simbolo = 'S/';
        $empresa = \App\Models\Configuracion::empresa();
        if ($empresa && $empresa->simbolo_moneda) {
            $simbolo = $empresa->simbolo_moneda;
        }
        return $simbolo . ' ' . self::monto($monto);
    }

    /**
     * Formatea un monto con símbolo de moneda SIN decimales.
     */
    public static function monedaEntero($monto): string
    {
        $simbolo = 'S/';
        $empresa = \App\Models\Configuracion::empresa();
        if ($empresa && $empresa->simbolo_moneda) {
            $simbolo = $empresa->simbolo_moneda;
        }
        return $simbolo . ' ' . self::montoEntero($monto);
    }
}