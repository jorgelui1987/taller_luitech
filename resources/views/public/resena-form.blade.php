<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deja tu reseña - {{ $config->nombre_tienda ?? $tenant->empresa ?? 'Tienda' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" integrity="sha384-4LISF5TTJX/fLmGSxO53rV4miRxdg84mZsxmO8Rx5jGtp/LbrixFETvWa5a6sESd" crossorigin="anonymous">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { border-radius: 20px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; width: 100%; }
        .star-input { font-size: 40px; cursor: pointer; color: #d1d5db; transition: color .2s; }
        .star-input.active { color: #f59e0b; }
        .star-input:hover { color: #f59e0b; }
        .btn-primary { background: linear-gradient(135deg, #0891b2, #0e7490); border: none; border-radius: 10px; padding: 12px; font-weight: 600; }
        .btn-primary:hover { opacity: .9; }
    </style>
</head>
<body>
    <div class="card p-4">
        <div class="text-center mb-4">
            <div style="font-size:50px;">⭐</div>
            <h3 class="fw-bold mt-2" style="color:#0f172a;">¿Cómo fue tu experiencia?</h3>
            <p class="text-muted">Tu opinión nos ayuda a mejorar</p>
            @if($reparacion)
                <div class="p-2" style="background:#f3f4f6;border-radius:10px;font-size:13px;">
                    <strong>Orden:</strong> {{ $reparacion->numero_orden }} · {{ $reparacion->dispositivo }} {{ $reparacion->marca }}
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('public.resena.store', $tenant->slug_publico) }}" method="POST">
            @csrf
            @if($reparacion)
                <input type="hidden" name="reparacion_id" value="{{ $reparacion->id }}">
            @endif

            <div class="text-center mb-4">
                <label class="form-label fw-bold">Calificación</label>
                <div class="d-flex justify-content-center gap-2" id="starContainer">
                    @for($i=1; $i<=5; $i++)
                        <span class="star-input" data-value="{{ $i }}" onclick="selectStar({{ $i }})">★</span>
                    @endfor
                </div>
                <input type="hidden" name="calificacion" id="calificacion" value="5" required>
                @error('calificacion')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Tu nombre (opcional)</label>
                <input type="text" name="nombre_publico" class="form-control" placeholder="Ej: Juan Pérez" maxlength="100">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Comentario (opcional)</label>
                <textarea name="comentario" class="form-control" rows="4" placeholder="Cuéntanos cómo fue tu experiencia..." maxlength="1000"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-send me-2"></i>Enviar reseña
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('public.tienda', $tenant->slug_publico) }}" class="text-muted small" style="text-decoration:none;">
                ← Volver a la tienda
            </a>
        </div>
    </div>

    <script>
        function selectStar(value) {
            document.getElementById('calificacion').value = value;
            document.querySelectorAll('.star-input').forEach((el, i) => {
                el.classList.toggle('active', i < value);
            });
        }
        // Inicializar con 5 estrellas
        selectStar(5);
    </script>
</body>
</html>
