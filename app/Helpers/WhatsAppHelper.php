<?php

namespace App\Helpers;

use App\Models\Configuracion;

class WhatsAppHelper
{
    /**
     * Obtiene el código de país desde la configuración de la empresa.
     * Toma el código del número de WhatsApp configurado o del teléfono.
     */
    private static function obtenerCodigoPais(): string
    {
        try {
            $empresa = Configuracion::empresa();
            $numeroBase = $empresa->whatsapp ?? $empresa->telefono ?? '51';
            $numero = preg_replace('/[^0-9]/', '', $numeroBase);
            // Si tiene más de 10 dígitos, los primeros son el código de país
            if (strlen($numero) > 10) {
                return substr($numero, 0, strlen($numero) - 9);
            }
        } catch (\Exception $e) {
            // Si falla, usar código por defecto
        }
        return '51'; // Default: Perú
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
        $numero = preg_replace('/[^0-9]/', '', $telefono);

        if (empty($numero)) {
            return null;
        }

        // Obtener código de país de la configuración
        $codigoPais = self::obtenerCodigoPais();

        // Si el número es solo local (9 dígitos para Perú), anteponer código de país
        if (strlen($numero) <= 10) {
            $numero = $codigoPais . $numero;
        }
        // Si ya tiene código de país (ej: 51999999999 -> 11 dígitos), usarlo tal cual

        $mensajeCodificado = urlencode($mensaje);

        return "https://wa.me/{$numero}?text={$mensajeCodificado}";
    }

    /**
     * Genera el mensaje de "Recibido" para una orden de reparación.
     */
    public static function mensajeRecibido($reparacion, string $nombreTienda = 'CRM Celulares', ?string $urlEstado = null): string
    {
        $mensaje = "🔧 *{$nombreTienda} - Orden de Reparación*\n\n" .
            "📋 N° Orden: {$reparacion->numero_orden}\n" .
            "📱 Equipo: {$reparacion->dispositivo} {$reparacion->marca} {$reparacion->modelo}\n" .
            "📝 Falla: {$reparacion->falla_reportada}\n" .
            "📅 Recibido: " . optional($reparacion->fecha_recepcion)->format('d/m/Y H:i') . "\n\n" .
            "✅ Su equipo ha sido recibido en nuestro taller. Le mantendremos informado del avance. ¡Gracias!";

        if ($urlEstado) {
            $mensaje .= "\n\n🔗 *Estado en línea:*\n{$urlEstado}";
        }

        return $mensaje;
    }

    /**
     * Genera el mensaje de "Listo para entregar" para una orden de reparación.
     */
    public static function mensajeListo($reparacion, string $nombreTienda = 'CRM Celulares', ?string $urlEstado = null): string
    {
        $costo = number_format($reparacion->costo_final ?: $reparacion->presupuesto ?: 0, 2);

        $mensaje = "🔧 *{$nombreTienda} - Orden de Reparación*\n\n" .
            "📋 N° Orden: {$reparacion->numero_orden}\n" .
            "📱 Equipo: {$reparacion->dispositivo} {$reparacion->marca} {$reparacion->modelo}\n" .
            "✅ *¡Su equipo está listo para recoger!*\n" .
            "💰 Costo: S/ {$costo}\n\n" .
            "📍 Lo esperamos en nuestro local para realizar la entrega. ¡Gracias por su preferencia!";

        if ($urlEstado) {
            $mensaje .= "\n\n🔗 *Estado en línea:*\n{$urlEstado}";
        }

        return $mensaje;
    }

    /**
     * Obtiene el número de teléfono del cliente limpiando caracteres no numéricos.
     */
    public static function limpiarNumero(?string $telefono): string
    {
        return preg_replace('/[^0-9]/', '', $telefono ?? '');
    }
}