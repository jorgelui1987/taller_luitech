<?php

use App\Models\Configuracion;

if (!function_exists('formato_monto')) {
    /**
     * Formatea un monto CON 2 decimales (para facturas, ventas, tickets).
     */
    function formato_monto($monto): string
    {
        return number_format((float)$monto, 2);
    }
}

if (!function_exists('formato_monto_entero')) {
    /**
     * Formatea un monto SIN decimales (para dashboard, tarjetas, gráficos).
     */
    function formato_monto_entero($monto): string
    {
        return number_format((float)$monto, 0);
    }
}

if (!function_exists('formato_moneda')) {
    /**
     * Formatea un monto con símbolo de moneda y 2 decimales.
     */
    function formato_moneda($monto): string
    {
        $simbolo = 'S/';
        $empresa = Configuracion::empresa();
        if ($empresa && $empresa->simbolo_moneda) {
            $simbolo = $empresa->simbolo_moneda;
        }
        return $simbolo . ' ' . formato_monto($monto);
    }
}

if (!function_exists('formato_moneda_entero')) {
    /**
     * Formatea un monto con símbolo de moneda SIN decimales.
     */
    function formato_moneda_entero($monto): string
    {
        $simbolo = 'S/';
        $empresa = Configuracion::empresa();
        if ($empresa && $empresa->simbolo_moneda) {
            $simbolo = $empresa->simbolo_moneda;
        }
        return $simbolo . ' ' . formato_monto_entero($monto);
    }
}