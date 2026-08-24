<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Recuperación SuperAdmin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            max-width: 560px;
            width: 100%;
            padding: 32px;
        }
        h1 { font-size: 20px; margin-bottom: 6px; color: #f8fafc; }
        .sub { font-size: 13px; color: #94a3b8; margin-bottom: 24px; }
        .alerta { border-radius: 8px; padding: 12px 14px; font-size: 14px; margin-bottom: 16px; }
        .alerta.success { background: #064e3b; color: #6ee7b7; border: 1px solid #10b981; }
        .alerta.error { background: #7f1d1d; color: #fca5a5; border: 1px solid #ef4444; }
        .diag { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px; font-size: 13px; margin-bottom: 24px; }
        .diag h2 { font-size: 13px; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; margin-bottom: 10px; }
        .ok { color: #34d399; } .bad { color: #f87171; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #1e293b; font-weight: normal; }
        th { color: #94a3b8; font-size: 12px; }
        label { display: block; font-size: 13px; color: #cbd5e1; margin: 14px 0 6px; }
        input[type=email], input[type=password] {
            width: 100%; padding: 10px 12px; border-radius: 8px;
            border: 1px solid #475569; background: #0f172a; color: #e2e8f0; font-size: 14px;
        }
        input:focus { outline: none; border-color: #3b82f6; }
        button {
            margin-top: 22px; width: 100%; padding: 12px; border: none; border-radius: 8px;
            background: #2563eb; color: white; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        .nota { font-size: 12px; color: #64748b; margin-top: 18px; line-height: 1.5; }
        .err { color: #f87171; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body>
<div class="card">
    <h1>🔐 Recuperación de acceso SuperAdmin</h1>
    <p class="sub">Herramienta de emergencia — usar solo si no tienes acceso al panel ni a la terminal.</p>

    @if (session('success'))
        <div class="alerta success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alerta error">{{ session('error') }}</div>
    @endif

    <div class="diag">
        <h2>Diagnóstico de base de datos</h2>
        @if ($diagnostico['conexion_ok'])
            <p class="ok">✓ Conexión a la base de datos correcta.</p>
            <p>Total de usuarios: <strong>{{ $diagnostico['total_users'] }}</strong></p>

            @if (count($diagnostico['superadmins']) > 0)
                <table>
                    <tr><th>ID</th><th>Email superadmin existente</th><th>Activo</th></tr>
                    @foreach ($diagnostico['superadmins'] as $s)
                        <tr>
                            <td>{{ $s['id'] }}</td>
                            <td>{{ $s['email'] }}</td>
                            <td class="{{ $s['activo'] ? 'ok' : 'bad' }}">{{ $s['activo'] ? 'Sí' : 'No' }}</td>
                        </tr>
                    @endforeach
                </table>
                <p style="margin-top:8px; color:#94a3b8;">Usa uno de estos emails en el formulario para restablecer su contraseña, o crea uno nuevo.</p>
            @else
                <p class="bad">⚠ No existe ningún usuario con rol superadmin. Crea uno con el formulario.</p>
            @endif
        @else
            <p class="bad">✗ Sin conexión a la base de datos:</p>
            <p style="color:#fca5a5; word-break:break-all;">{{ $diagnostico['error'] }}</p>
        @endif
    </div>

    @if ($diagnostico['conexion_ok'])
        <form method="POST" action="{{ route('superadmin.recuperar.post', ['token' => $token]) }}">
            @csrf
            <label for="email">Email del SuperAdmin</label>
            <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="admin@ejemplo.com">
            @error('email')<div class="err">{{ $message }}</div>@enderror

            <label for="password">Nueva contraseña (mínimo 8 caracteres)</label>
            <input type="password" id="password" name="password" required minlength="8">
            @error('password')<div class="err">{{ $message }}</div>@enderror

            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">

            <button type="submit">Restablecer SuperAdmin</button>
        </form>

        <p class="nota">
            ⚠️ Después de recuperar el acceso, elimina la variable <code>SUPERADMIN_RECOVERY_TOKEN</code>
            de Dokploy → Environment y redespliega para desactivar esta página.
        </p>
    @endif
</div>
</body>
</html>