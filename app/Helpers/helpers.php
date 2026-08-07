<?php

use App\Models\Configuracion;

if (!function_exists('formatoMonto')) {
    /**
     * Formatea un monto CON 2 decimales (para facturas, ventas, tickets).
     */
    function formatoMonto($monto): string
    {
        return number_format((float)$monto, 2);
    }
}

if (!function_exists('formatoMontoEntero')) {
    /**
     * Formatea un monto SIN decimales (para dashboard, tarjetas, gráficos).
     */
    function formatoMontoEntero($monto): string
    {
        return number_format((float)$monto, 0);
    }
}

if (!function_exists('formatoMoneda')) {
    /**
     * Formatea un monto con símbolo de moneda y 2 decimales.
     */
    function formatoMoneda($monto): string
    {
        $simbolo = 'S/';
        $empresa = Configuracion::empresa();
        if ($empresa && $empresa->simbolo_moneda) {
            $simbolo = $empresa->simbolo_moneda;
        }
        return $simbolo . ' ' . formatoMonto($monto);
    }
}

if (!function_exists('formatoMonedaEntero')) {
    /**
     * Formatea un monto con símbolo de moneda SIN decimales.
     */
    function formatoMonedaEntero($monto): string
    {
        $simbolo = 'S/';
        $empresa = Configuracion::empresa();
        if ($empresa && $empresa->simbolo_moneda) {
            $simbolo = $empresa->simbolo_moneda;
        }
        return $simbolo . ' ' . formatoMontoEntero($monto);
    }
}
