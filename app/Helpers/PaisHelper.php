<?php

namespace App\Helpers;

use App\Models\Configuracion;

/**
 * Helper centralizado para manejar la lógica multi-país.
 * Permite que el sistema funcione con Perú, Chile y otros países.
 */
class PaisHelper
{
    /**
     * Configuración por país: moneda, símbolo, impuesto, código WhatsApp, zona horaria.
     */
    public static function configuracionPorPais(string $pais): array
    {
        $configs = [
            'PE' => [
                'pais'           => 'PE',
                'nombre_pais'    => 'Perú',
                'moneda'         => 'PEN',
                'simbolo_moneda' => 'S/',
                'impuesto'       => 18.00,
                'nombre_impuesto'=> 'IGV',
                'codigo_whatsapp'=> '51',
                'zona_horaria'   => 'America/Lima',
                'documento'      => 'DNI/RUC',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia', 'cuotas', 'yape', 'plin'],
            ],
            'CL' => [
                'pais'           => 'CL',
                'nombre_pais'    => 'Chile',
                'moneda'         => 'CLP',
                'simbolo_moneda' => '$',
                'impuesto'       => 19.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '56',
                'zona_horaria'   => 'America/Santiago',
                'documento'      => 'RUT',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia', 'debito', 'credito'],
            ],
            'AR' => [
                'pais'           => 'AR',
                'nombre_pais'    => 'Argentina',
                'moneda'         => 'ARS',
                'simbolo_moneda' => '$',
                'impuesto'       => 21.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '54',
                'zona_horaria'   => 'America/Argentina/Buenos_Aires',
                'documento'      => 'CUIL/CUIT',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia'],
            ],
            'MX' => [
                'pais'           => 'MX',
                'nombre_pais'    => 'México',
                'moneda'         => 'MXN',
                'simbolo_moneda' => '$',
                'impuesto'       => 16.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '52',
                'zona_horaria'   => 'America/Mexico_City',
                'documento'      => 'RFC',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia'],
            ],
            'CO' => [
                'pais'           => 'CO',
                'nombre_pais'    => 'Colombia',
                'moneda'         => 'COP',
                'simbolo_moneda' => '$',
                'impuesto'       => 19.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '57',
                'zona_horaria'   => 'America/Bogota',
                'documento'      => 'Cédula/NIT',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia'],
            ],
            'EC' => [
                'pais'           => 'EC',
                'nombre_pais'    => 'Ecuador',
                'moneda'         => 'USD',
                'simbolo_moneda' => '$',
                'impuesto'       => 12.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '593',
                'zona_horaria'   => 'America/Guayaquil',
                'documento'      => 'Cédula/RUC',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia'],
            ],
            'BO' => [
                'pais'           => 'BO',
                'nombre_pais'    => 'Bolivia',
                'moneda'         => 'BOB',
                'simbolo_moneda' => 'Bs.',
                'impuesto'       => 13.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '591',
                'zona_horaria'   => 'America/La_Paz',
                'documento'      => 'CI/NIT',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia'],
            ],
            'UY' => [
                'pais'           => 'UY',
                'nombre_pais'    => 'Uruguay',
                'moneda'         => 'UYU',
                'simbolo_moneda' => '$U',
                'impuesto'       => 22.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '598',
                'zona_horaria'   => 'America/Montevideo',
                'documento'      => 'CI/RUT',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia'],
            ],
            'PY' => [
                'pais'           => 'PY',
                'nombre_pais'    => 'Paraguay',
                'moneda'         => 'PYG',
                'simbolo_moneda' => '₲',
                'impuesto'       => 10.00,
                'nombre_impuesto'=> 'IVA',
                'codigo_whatsapp'=> '595',
                'zona_horaria'   => 'America/Asuncion',
                'documento'      => 'CI/RUC',
                'metodos_pago'   => ['efectivo', 'tarjeta', 'transferencia'],
            ],
        ];

        return $configs[$pais] ?? $configs['PE'];
    }

    /**
     * Obtiene la configuración del país de la empresa actual.
     */
    public static function configuracionActual(): array
    {
        try {
            $empresa = Configuracion::empresa();
            $pais = $empresa?->pais ?? 'CL';
        } catch (\Exception $e) {
            $pais = 'CL';
        }

        return self::configuracionPorPais($pais);
    }

    /**
     * Obtiene el símbolo de moneda de la empresa actual (o por código de moneda).
     */
    public static function simboloMoneda(?string $moneda = null): string
    {
        if ($moneda) {
            $mapa = [
                'PEN' => 'S/', 'CLP' => '$', 'USD' => '$', 'EUR' => '€',
                'MXN' => '$', 'COP' => '$', 'ARS' => '$', 'BOB' => 'Bs.',
                'UYU' => '$U', 'PYG' => '₲', 'BRL' => 'R$', 'GBP' => '£',
            ];
            return $mapa[$moneda] ?? '$';
        }

        $config = self::configuracionActual();
        return $config['simbolo_moneda'];
    }

    /**
     * Obtiene el código de país de WhatsApp (+51 Perú, +56 Chile, etc).
     */
    public static function codigoWhatsapp(): string
    {
        $config = self::configuracionActual();
        return $config['codigo_whatsapp'];
    }

    /**
     * Obtiene el nombre del impuesto (IGV, IVA, etc).
     */
    public static function nombreImpuesto(): string
    {
        $config = self::configuracionActual();
        return $config['nombre_impuesto'];
    }

    /**
     * Valida un RUT chileno (Módulo 11).
     *
     * @param string $rut Ejemplo: 12345678 o 12.345.678-K
     * @return bool
     */
    public static function validarRut(string $rut): bool
    {
        $rut = preg_replace('/[^0-9kK]/', '', $rut);
        if (strlen($rut) < 2) {
            return false;
        }

        $cuerpo = (int) substr($rut, 0, -1);
        $dv = strtoupper(substr($rut, -1));

        $suma = 0;
        $multiplo = 2;
        $temp = $cuerpo;

        while ($temp > 0) {
            $suma += ($temp % 10) * $multiplo;
            $temp = (int) ($temp / 10);
            $multiplo = $multiplo == 7 ? 2 : $multiplo + 1;
        }

        $resto = $suma % 11;
        $dvCalculado = 11 - $resto;

        if ($dvCalculado == 11) {
            $dvCalculado = '0';
        } elseif ($dvCalculado == 10) {
            $dvCalculado = 'K';
        } else {
            $dvCalculado = (string) $dvCalculado;
        }

        return $dv === $dvCalculado;
    }

    /**
     * Genera el dígito verificador (DV) de un RUT chileno.
     */
    public static function generarDigitoVerificador(int $cuerpo): string
    {
        $suma = 0;
        $multiplo = 2;
        $temp = $cuerpo;

        while ($temp > 0) {
            $suma += ($temp % 10) * $multiplo;
            $temp = (int) ($temp / 10);
            $multiplo = $multiplo == 7 ? 2 : $multiplo + 1;
        }

        $resto = $suma % 11;
        $dv = 11 - $resto;

        if ($dv == 11) {
            return '0';
        } elseif ($dv == 10) {
            return 'K';
        }

        return (string) $dv;
    }
}