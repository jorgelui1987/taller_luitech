<?php

use App\Models\Configuracion;

if (!function_exists('formato_monto')) {
    /**
     * Formatea un monto según los decimales configurados en la empresa.
     */
    function formato_monto($monto): string
    {
        $decimales = 2;
        $empresa = Configuracion::empresa();
        if ($empresa && $empresa->decimales !== null) {
            $decimales = (int)$empresa->decimales;
        }
        return number_format((float)$monto, $decimales);
    }
}

if (!function_exists('formato_moneda')) {
    /**
     * Formatea un monto con el símbolo de la moneda configurada.
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