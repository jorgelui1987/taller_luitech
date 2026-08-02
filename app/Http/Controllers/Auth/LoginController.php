<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    private function rateLimitKey(string $email, Request $request): string
    {
        return 'login:' . md5($request->ip() . ':' . strtolower($email));
    }

    private function incrementRateLimit(string $key): void
    {
        $attempts = Cache::get($key, 0) + 1;
        Cache::put($key, $attempts, 60);
    }

    private function isRateLimited(string $key): bool
    {
        return (int) Cache::get($key, 0) >= 5;
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

        if ($this->isRateLimited($throttledKey)) {
            return back()->withErrors([
                'email' => 'Demasiados intentos. Intenta nuevamente en unos minutos.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (!Auth::user()->activo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        $this->incrementRateLimit($throttledKey);

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
