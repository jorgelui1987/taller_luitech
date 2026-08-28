<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación en Dos Pasos — CRM Tienda Celulares</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #2d1b69 50%, #083344 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .challenge-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0,0,0,.4);
        }
        .challenge-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #0891b2, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 28px;
            margin: 0 auto 20px;
        }
        .form-control {
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            padding: 10px 16px;
            font-size: 13.5px;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: #0891b2;
            box-shadow: 0 0 0 3px rgba(6, 182, 212,.15);
            outline: none;
        }
        .btn-verify {
            background: linear-gradient(135deg, #0891b2, #3b82f6);
            border: none;
            border-radius: 10px;
            padding: 11px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            width: 100%;
            cursor: pointer;
            transition: opacity .2s, transform .2s;
        }
        .btn-verify:hover { opacity: .92; transform: translateY(-1px); }
        .text-accent { color: #0891b2; }
    </style>
</head>
<body>
    <div class="challenge-card">
        <div class="challenge-icon">
            <i class="fas fa-shield-halved"></i>
        </div>

        <h2 class="text-center mb-2" style="font-size:20px; font-weight:700; color:#0f172a;">Verificación en Dos Pasos</h2>
        <p class="text-center text-muted mb-4" style="font-size:13px;">
            Ingresa el código de 6 dígitos de tu aplicación de autenticación.
        </p>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" style="font-size:13px; border-radius:10px;">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('two-factor.verify-challenge') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="codigo" class="form-label" style="font-size:13px; font-weight:500; color:#374151;">
                    Código de autenticación
                </label>
                <input type="text" name="codigo" id="codigo" class="form-control text-center"
                       placeholder="000000" required pattern="\d{6}" maxlength="6" autofocus
                       style="font-size:1.5rem; letter-spacing:8px;">
            </div>
            <button type="submit" class="btn-verify">
                <i class="fas fa-check-circle me-2"></i>Verificar
            </button>
        </form>

        <hr class="my-4">

        <p class="text-center mb-2" style="font-size:12px; color:#6b7280;">
            ¿Perdiste tu aplicación de autenticación?
        </p>

        <form action="{{ route('two-factor.verify-challenge') }}" method="POST">
            @csrf
            <div class="mb-2">
                <label for="recovery_code" class="form-label" style="font-size:12px; color:#6b7280;">
                    Código de recuperación
                </label>
                <div class="input-group">
                    <input type="text" name="recovery_code" id="recovery_code" class="form-control"
                           placeholder="Código de recuperación" style="font-size:13px;">
                    <button type="submit" class="btn btn-outline-secondary" style="font-size:13px;">
                        Usar
                    </button>
                </div>
            </div>
        </form>

        <p class="text-center mt-3 mb-0">
            <a href="{{ route('logout') }}" class="text-accent text-decoration-none" style="font-size:13px;"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Cancelar y volver al login
            </a>
        </p>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</body>
</html>
