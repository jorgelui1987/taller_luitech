<?php

namespace App\Helpers;

class FormatoHelper
{
    /**
     * Formatea un monto SIN decimales (entero).
     */
    public static function monto($monto): string
    {
        return number_format((float)$monto, 0);
    }

    /**
     * Formatea un monto con el símbolo de la moneda (sin decimales).
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
}