<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - CRM Celulares</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
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
                                <label for="empresa" class="form-label">Nombre de tu tienda o empresa *</label>
                                <input type="text" name="empresa" id="empresa" class="form-control @error('empresa') is-invalid @enderror" value="{{ old('empresa') }}" required placeholder="Ej: Celulares García">
                                @error('empresa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <h5 class="border-bottom pb-2 mt-4">Datos del administrador</h5>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre completo *</label>
                                <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" required placeholder="Ej: Juan Pérez">
                                @error('nombre') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico *</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="tucorreo@ejemplo.com">
                                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña *</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)" title="Mostrar/Ocultar contraseña">
                                        <svg id="icon-password" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                            <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                        </svg>
                                    </button>
                                    @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar contraseña *</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)" title="Mostrar/Ocultar contraseña">
                                        <svg id="icon-password_confirmation" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                            <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                        </svg>
                                    </button>
                                </div>
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

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById('icon-' + inputId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>' +
                      '<path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>' +
                      '<path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.185.288c.335.48.83 1.12 1.465 1.755C4.609 11.332 6.073 12.5 8 12.5c.616 0 1.214-.108 1.785-.29l.77.771A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-2.721 2.641-4.238l.708.709z"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>' +
                      '<path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>';
            }
        }
    </script>
</body>
</html>
