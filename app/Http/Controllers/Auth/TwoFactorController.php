<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\TwoFactorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    /**
     * Mostrar la configuración de 2FA.
     */
    public function registro()
    {
        $user = Auth::user();

        // Si ya tiene 2FA activado, mostrar opción de desactivar
        return view('auth.two-factor.setup', [
            'user' => $user,
            'secret' => $user->two_factor_secret,
            'qrUrl' => $user->two_factor_secret
                ? TwoFactorHelper::generarQRUrl($user, $user->two_factor_secret)
                : null,
            'recoveryCodes' => $user->two_factor_recovery_codes
                ? json_decode($user->two_factor_recovery_codes, true)
                : [],
        ]);
    }

    /**
     * Generar un secreto nuevo para configurar 2FA.
     */
    public function generar(Request $request)
    {
        $user = Auth::user();

        $secret = TwoFactorHelper::generarSecreto();
        $recoveryCodes = TwoFactorHelper::generarCodigosRecuperacion(10);

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
            'two_factor_confirmed_at' => null, // No confirmado hasta que verifique
        ]);

        return response()->json([
            'secret' => $secret,
            'qr_url' => TwoFactorHelper::generarQRUrl($user, $secret),
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Confirmar el código y activar 2FA.
     */
    public function confirmar(Request $request)
    {
        $user = Auth::user();
        $codigo = $request->input('codigo');

        if (!$user->two_factor_secret) {
            return back()->with('error', 'Primero genera un secreto para configurar 2FA.');
        }

        if (!TwoFactorHelper::verificarCodigo($user->two_factor_secret, $codigo)) {
            return back()->with('error', 'El código ingresado no es válido. Intenta nuevamente.');
        }

        $user->update([
            'two_factor_confirmed_at' => now(),
        ]);

        \App\Helpers\AuditoriaHelper::registrar('activar_2fa', 'user', $user->id, 'Usuario activó verificación en dos pasos');

        return redirect()->route('two-factor.setup')
            ->with('success', '¡2FA activado correctamente!');
    }

    /**
     * Desactivar 2FA.
     */
    public function desactivar(Request $request)
    {
        $user = Auth::user();

        $codigo = $request->input('codigo');

        if (!$user->two_factor_secret) {
            return back()->with('error', 'El 2FA no está activado.');
        }

        if (!TwoFactorHelper::verificarCodigo($user->two_factor_secret, $codigo)) {
            return back()->with('error', 'El código ingresado no es válido. No se pudo desactivar 2FA.');
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        \App\Helpers\AuditoriaHelper::registrar('desactivar_2fa', 'user', $user->id, 'Usuario desactivó verificación en dos pasos');

        return redirect()->route('two-factor.setup')
            ->with('success', '2FA desactivado correctamente.');
    }

    /**
     * Vistas de verificación durante el login (challenge).
     */
    public function showChallenge()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verificar el código durante el login (challenge).
     */
    public function verifyChallenge(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user || !$user->two_factor_secret) {
            session()->forget('2fa_user_id');
            return redirect()->route('login');
        }

        $codigo = $request->input('codigo');
        $recovery = $request->input('recovery_code');

        $valido = false;

        if ($codigo) {
            $valido = TwoFactorHelper::verificarCodigo($user->two_factor_secret, $codigo);
        } elseif ($recovery) {
            $valido = TwoFactorHelper::verificarCodigoRecuperacion($user, $recovery);
        }

        if (!$valido) {
            return back()->with('error', 'El código ingresado no es válido. Intenta nuevamente.');
        }

        session()->forget('2fa_user_id');
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}