<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    private function rateLimitKey(string $email, Request $request): string
    {
        return 'login:' . hash('sha256', $request->ip() . ':' . strtolower($email));
    }

    private function lockoutKey(string $email, Request $request): string
    {
        return 'login_lockout:' . hash('sha256', $request->ip() . ':' . strtolower($email));
    }

    private function incrementRateLimit(string $key): void
    {
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, 60);

        // Si alcanza el máximo de intentos, bloquear temporalmente
        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutKey = str_replace('login:', 'login_lockout:', $key);
            Cache::put($lockoutKey, true, self::LOCKOUT_MINUTES * 60);
        }
    }

    private function isRateLimited(string $key): bool
    {
        return (int) Cache::get($key, 0) >= self::MAX_ATTEMPTS;
    }

    private function isLockedOut(string $key): bool
    {
        $lockoutKey = str_replace('login:', 'login_lockout:', $key);
        return (bool) Cache::get($lockoutKey, false);
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email:rfc,dns'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $throttledKey = $this->rateLimitKey($credentials['email'], $request);

        $error = $this->validarCredenciales($credentials, $throttledKey);
        if ($error) {
            return $error;
        }

        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        // Si el usuario tiene 2FA activado, no autenticar aún - mostrar challenge
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            $request->session()->put('2fa_user_id', $user->id);
            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function validarCredenciales(array $credentials, string $throttledKey): ?RedirectResponse
    {
        if ($this->isLockedOut($throttledKey)) {
            return back()->withErrors([
                'email' => 'Cuenta bloqueada temporalmente por demasiados intentos. Intenta en ' . self::LOCKOUT_MINUTES . ' minutos.',
            ])->onlyInput('email');
        }

        if ($this->isRateLimited($throttledKey)) {
            return back()->withErrors([
                'email' => 'Demasiados intentos. Intenta nuevamente en unos minutos.',
            ])->onlyInput('email');
        }

        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        if (!$user || !Auth::getProvider()->validateCredentials($user, $credentials)) {
            $this->incrementRateLimit($throttledKey);
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        if (!$user->activo) {
            return back()->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ]);
        }

        return null;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
