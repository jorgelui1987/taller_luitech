<?php

namespace App\Helpers;

use App\Models\Configuracion;
use App\Helpers\PaisHelper;

class WhatsAppHelper
{
    private const REGEX_SOLO_DIGITOS = '/\D/';

    /**
     * Obtiene el código de país desde la configuración de la empresa.
     * Toma el código del número de WhatsApp configurado o del teléfono.
     */
    private static function obtenerCodigoPais(): string
    {
        try {
            $empresa = Configuracion::empresa();
            $numeroBase = $empresa->whatsapp ?? $empresa->telefono ?? '51';
            $numero = preg_replace(self::REGEX_SOLO_DIGITOS, '', $numeroBase);
            // Si tiene más de 10 dígitos, los primeros son el código de país
            if (strlen($numero) > 10) {
                return substr($numero, 0, strlen($numero) - 9);
            }
        } catch (\Exception $e) {
            // Si falla, usar código por defecto
        }
        return PaisHelper::codigoWhatsapp();
    }

    /**
     * Genera una URL de WhatsApp con el mensaje precompuesto.
     *
     * @param string|null $telefono Número de teléfono del cliente
     * @param string $mensaje Mensaje a enviar (texto plano, se codificará automáticamente)
     * @return string|null URL de WhatsApp o null si no hay teléfono
     */
    public static function generarUrl(?string $telefono, string $mensaje): ?string
    {
        if (empty($telefono)) {
            return null;
        }

        // Limpiar el número: solo dígitos
        $numero = preg_replace(self::REGEX_SOLO_DIGITOS, '', $telefono);

        if (empty($numero)) {
            return null;
        }

        // Obtener código de país de la configuración
        $codigoPais = self::obtenerCodigoPais();

        // Si el número es solo local, anteponer código de país
        if (strlen($numero) <= 10) {
            $numero = $codigoPais . $numero;
        }
        // Si ya tiene código de país (ej: 51999999999 -> 11 dígitos), usarlo tal cual

        $mensajeCodificado = urlencode($mensaje);

        return "https://wa.me/{$numero}?text={$mensajeCodificado}";
    }

    /**
     * Obtiene la URL de la mini página web del tenant de la reparación.
     */
    private static function obtenerUrlMiniWeb($reparacion): ?string
    {
        try {
            $tenant = $reparacion->tenant;
            if ($tenant && $tenant->slug_publico) {
                return url('/t/' . $tenant->slug_publico);
            }
        } catch (\Exception $e) {
            // Si falla, no incluir la URL
        }
        return null;
    }

    /**
     * Genera el mensaje de "Recibido" para una orden de reparación.
     * Formato moderno: negritas + emojis (sin líneas ASCII).
     */
    public static function mensajeRecibido($reparacion, string $nombreTienda = 'CRM Celulares', ?string $urlEstado = null): string
    {
        $clienteNombre = $reparacion->cliente?->nombre_completo ?? '';
        $saludo = $clienteNombre ? "Hola *{$clienteNombre}* 👋\n\n" : '';

        $mensaje = "🔧 *{$nombreTienda}*\n" .
            "\n📋 *ORDEN {$reparacion->numero_orden}* — Recibida\n" .
            "📱 Equipo: {$reparacion->dispositivo} {$reparacion->marca} {$reparacion->modelo}\n" .
            "⚠️ Falla: {$reparacion->falla_reportada}\n" .
            "📅 Recibido: " . optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') . "\n\n" .
            "✅ Su equipo ha sido recibido en nuestro taller. Le mantendremos informado del avance. ¡Gracias!";

        if ($urlEstado) {
            $mensaje .= "\n\n🔗 *Sigue tu reparación aquí:*\n{$urlEstado}";
        }

        $urlMiniWeb = self::obtenerUrlMiniWeb($reparacion);
        if ($urlMiniWeb) {
            $mensaje .= "\n\n🌐 *Visita nuestra tienda:*\n{$urlMiniWeb}";
        }

        return $saludo . $mensaje;
    }

    /**
     * Genera el mensaje de "Listo para entregar" para una orden de reparación.
     * Formato moderno: negritas + emojis (sin líneas ASCII).
     */
    public static function mensajeListo($reparacion, string $nombreTienda = 'CRM Celulares', ?string $urlEstado = null): string
    {
        $costo = number_format($reparacion->costo_final ?: $reparacion->presupuesto ?: 0, 2);
        $simbolo = PaisHelper::simboloMoneda();

        $clienteNombre = $reparacion->cliente?->nombre_completo ?? '';
        $saludo = $clienteNombre ? "Hola *{$clienteNombre}* 👋\n\n" : '';

        $mensaje = "🔧 *{$nombreTienda}*\n" .
            "\n📋 *ORDEN {$reparacion->numero_orden}*\n" .
            "📱 Equipo: {$reparacion->dispositivo} {$reparacion->marca} {$reparacion->modelo}\n" .
            "🎉 *¡Su equipo está listo para recoger!*\n" .
            "💰 Costo: {$simbolo} {$costo}\n\n" .
            "📍 Lo esperamos en nuestro local para realizar la entrega. ¡Gracias por su preferencia! 🙌";

        if ($urlEstado) {
            $mensaje .= "\n\n🔗 *Sigue tu reparación aquí:*\n{$urlEstado}";
        }

        $urlMiniWeb = self::obtenerUrlMiniWeb($reparacion);
        if ($urlMiniWeb) {
            $mensaje .= "\n\n🌐 *Visita nuestra tienda:*\n{$urlMiniWeb}";
        }

        return $saludo . $mensaje;
    }

    /**
     * Obtiene el número de teléfono del cliente limpiando caracteres no numéricos.
     */
    public static function limpiarNumero(?string $telefono): string
    {
        return preg_replace(self::REGEX_SOLO_DIGITOS, '', $telefono ?? '');
    }
}
