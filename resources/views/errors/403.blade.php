<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso No Autorizado (403) — CRM Tienda Celulares</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a0a3e 0%, #2d1254 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            margin: 0;
            padding: 20px;
        }
        .error-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 50px 30px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .error-code {
            font-size: 96px;
            font-weight: 800;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .error-desc {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 30px;
        }
        .btn-home {
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: #ffffff;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="mb-3">
            <i class="fas fa-lock" style="font-size: 48px; color: #f59e0b;"></i>
        </div>
        <div class="error-code">403</div>
        <h1 class="error-title">Acceso Prohibido</h1>
        <p class="error-desc">
            {{ $exception->getMessage() ?: 'No tienes permisos para acceder a esta sección o tu cuenta no está activa.' }}
        </p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="fas fa-home"></i> Volver al Inicio
        </a>
    </div>
</body>
</html>
