<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - CRM Celulares</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>📱 Crear tu cuenta gratuita</h4>
                        <p class="mb-0">Comienza a gestionar tu tienda de celulares</p>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="POST" action="{{ route('registro.tenant.store') }}">
                            @csrf

                            <div class="text-center mb-4">
                                <span class="badge bg-success" style="font-size:0.9rem; padding:8px 20px;">
                                    🌱 Plan Gratis incluido
                                </span>
                            </div>

                            <h5 class="border-bottom pb-2">Datos de la empresa</h5>
                            <div class="mb-3">
                                <label class="form-label">Nombre de tu tienda o empresa *</label>
                                <input type="text" name="empresa" class="form-control @error('empresa') is-invalid @enderror" value="{{ old('empresa') }}" required placeholder="Ej: Celulares García">
                                @error('empresa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <h5 class="border-bottom pb-2 mt-4">Datos del administrador</h5>
                            <div class="mb-3">
                                <label class="form-label">Nombre completo *</label>
                                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required placeholder="Ej: Juan Pérez">
                                @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Correo electrónico *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="tucorreo@ejemplo.com">
                                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirmar contraseña *</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" name="terminos" class="form-check-input" id="terminos" required>
                                <label class="form-check-label" for="terminos">Acepto los <a href="#" target="_blank">términos y condiciones</a></label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Crear cuenta gratuita</button>
                        </form>

                        <div class="text-center mt-3">
                            <p class="text-muted small">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
                        </div>
                    </div>
                </div>
                <p class="text-center text-muted small mt-3">
                    Al registrarte aceptas nuestros términos de servicio.
                    Recibirás un correo con los detalles de acceso.
                </p>
            </div>
        </div>
    </div>
</body>
</html>