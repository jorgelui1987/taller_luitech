<?php

namespace App\Helpers;

use App\Models\User;

class TwoFactorHelper
{
    private const OTP_LENGTH = 6;
    private const TIME_STEP = 30;

    /**
     * Genera un secreto TOTP base32 válido para Google Authenticator.
     */
    public static function generarSecreto(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[ord($bytes[$i]) % strlen($alphabet)];
        }
        return $secret;
    }

    /**
     * Genera la URL otpauth:// para mostrar el QR.
     */
    public static function generarQRUrl(User $user, string $secreto): string
    {
        $issuer = urlencode(config('app.name', 'CRM Celulares'));
        $email = urlencode($user->email);
        return "otpauth://totp/{$issuer}:{$email}?secret={$secreto}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Genera un código TOTP para un secreto dado.
     */
    public static function generarCodigo(string $secreto, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter = intdiv($timestamp, self::TIME_STEP);
        $binary = pack('N*', 0) . pack('N*', $counter);

        $hash = hash_hmac('sha1', $binary, self::base32Decode($secreto), true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $value = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $value % (10 ** self::OTP_LENGTH);
        return str_pad((string) $otp, self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica un código TOTP permitiendo +/- 1 período de tiempo.
     */
    public static function verificarCodigo(string $secreto, string $codigo, int $tolerancia = 1): bool
    {
        if (!preg_match('/^\d{' . self::OTP_LENGTH . '}$/', $codigo)) {
            return false;
        }

        $timestamp = time();
        for ($i = -$tolerancia; $i <= $tolerancia; $i++) {
            $expected = self::generarCodigo($secreto, $timestamp + ($i * self::TIME_STEP));
            if (hash_equals($expected, $codigo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera códigos de recuperación.
     */
    public static function generarCodigosRecuperacion(int $cantidad = 10): array
    {
        $codigos = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $codigos[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codigos;
    }

    /**
     * Verifica un código de recuperación y lo elimina si es válido.
     */
    public static function verificarCodigoRecuperacion(User $user, string $codigo): bool
    {
        $codigo = strtoupper(trim($codigo));
        $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true);

        if (!is_array($codes)) {
            return false;
        }

        foreach ($codes as $i => $saved) {
            if (hash_equals($saved, $codigo)) {
                unset($codes[$i]);
                $user->update([
                    'two_factor_recovery_codes' => json_encode(array_values($codes)),
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Decodifica base32 a binario.
     */
    private static function base32Decode(string $base32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper(rtrim($base32, '='));
        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($base32); $i++) {
            $char = strpos($alphabet, $base32[$i]);
            if ($char === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $char;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $result .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
                $bitsLeft -= 8;
            }
        }

        return $result;
    }
}
