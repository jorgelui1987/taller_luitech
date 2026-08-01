<?php

namespace App\Helpers;

use App\Models\Configuracion;

class FormatoHelper
{
    /**
     * Formatea un monto según los decimales configurados en la empresa.
     */
    public static function monto($monto): string
    {
        $decimales = 2;
        $empresa = Configuracion::empresa();
        if ($empresa && $empresa->decimales !== null) {
            $decimales = (int)$empresa->decimales;
        }
        return number_format((float)$monto, $decimales);
    }

    /**
     * Formatea un monto con el símbolo de la moneda.
     */
    public static function moneda($monto): string
    {
        $simbolo = 'S/';
        $empresa = Configuracion::empresa();
        if ($empresa && $empresa->simbolo_moneda) {
            $simbolo = $empresa->simbolo_moneda;
        }
        return $simbolo . ' ' . self::monto($monto);
    }
}