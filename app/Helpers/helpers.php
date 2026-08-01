<?php

use App\Models\Configuracion;

if (!function_exists('formato_monto')) {
    /**
     * Formatea un monto SIN decimales (entero).
     */
    function formato_monto($monto): string
    {
        return number_format((float)$monto, 0);
    }
}

if (!function_exists('formato_moneda')) {
    /**
     * Formatea un monto con el símbolo de la moneda configurada (sin decimales).
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