<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Herramienta desactivada</title>
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
            max-width: 620px;
            width: 100%;
            padding: 32px;
        }
        h1 { font-size: 20px; margin-bottom: 8px; color: #f8fafc; }
        p { font-size: 14px; color: #cbd5e1; line-height: 1.6; margin-bottom: 12px; }
        ol { margin: 16px 0 16px 22px; }
        li { font-size: 14px; color: #cbd5e1; line-height: 1.7; }
        code {
            background: #0f172a; border: 1px solid #334155; border-radius: 6px;
            padding: 2px 8px; font-size: 13px; color: #93c5fd; word-break: break-all;
        }
        .ok-box {
            background: #064e3b; border: 1px solid #10b981; color: #6ee7b7;
            border-radius: 8px; padding: 12px 14px; font-size: 13px; margin-top: 18px;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>🔒 Herramienta de recuperación desactivada</h1>
    <p>El código de recuperación <strong>SÍ está desplegado</strong>, pero la herramienta está desactivada porque la variable de entorno no está definida o el token no coincide.</p>

    <p><strong>Para activarla:</strong></p>
    <ol>
        <li>Ve a Dokploy → tu aplicación → <strong>Environment</strong>.</li>
        <li>Agrega la variable:<br><code>SUPERADMIN_RECOVERY_TOKEN=rescate-luitech-2026</code><br>(usa el mismo valor que pondrás en la URL).</li>
        <li>Guarda y haz clic en <strong>Redeploy/Deploy</strong>.</li>
        <li>Vuelve a abrir esta misma URL con tu token.</li>
    </ol>

    <div class="ok-box">
        ✓ Si estás viendo esta página, significa que el servidor ya ejecuta la versión actualizada del código.
    </div>
</div>
</body>
</html>