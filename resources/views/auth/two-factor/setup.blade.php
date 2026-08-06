@extends('layouts.app')

@section('title', 'Configurar 2FA')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-shield-halved me-2 text-primary"></i>Verificación en Dos Pasos (2FA)</h5>
                </div>
                <div class="card-body p-4">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($user->two_factor_confirmed_at)
                        {{-- 2FA ACTIVADO --}}
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>2FA Activo</strong> — Tu cuenta está protegida con verificación en dos pasos.
                            <small class="d-block mt-1">Activado: {{ $user->two_factor_confirmed_at->format('d/m/Y H:i') }}</small>
                        </div>

                        <h6 class="fw-bold mb-3">Códigos de Recuperación</h6>
                        <p class="text-muted small">Guarda estos códigos en un lugar seguro. Puedes usarlos si pierdes acceso a tu aplicación de autenticación.</p>

                        @if(count($recoveryCodes) > 0)
                            <div class="bg-light rounded p-3 mb-4">
                                <div class="row g-2">
                                    @foreach($recoveryCodes as $code)
                                        <div class="col-md-6">
                                            <code class="d-block p-2 bg-white border rounded text-center">{{ $code }}</code>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No tienes códigos de recuperación disponibles. Se generan al activar 2FA.
                            </div>
                        @endif

                        <hr>
                        <h6 class="fw-bold text-danger mb-3">Desactivar 2FA</h6>
                        <form action="{{ route('two-factor.desactivar') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label for="codigo" class="form-label">Código de verificación actual</label>
                                <input type="text" name="codigo" id="codigo" class="form-control" placeholder="000000" required pattern="\d{6}" maxlength="6">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-unlock me-1"></i> Desactivar 2FA
                                </button>
                            </div>
                        </form>

                    @elseif($secret)
                        {{-- PASO 2: Confirmar código --}}
                        <h5 class="fw-bold mb-4">Paso 2: Confirma el código</h5>

                        <div class="text-center mb-4">
                            @if($qrUrl)
                                <h6 class="mb-3">Escanea este QR con <strong>Google Authenticator</strong> o <strong>Authy</strong>:</h6>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($qrUrl) }}"
                                     alt="QR Code"
                                     class="img-thumbnail mb-3"
                                     style="width:220px;height:220px;">
                            @endif

                            <p class="mb-1"><strong>O ingresa manualmente el secreto:</strong></p>
                            <code class="bg-light p-2 rounded d-inline-block mb-3">{{ $secret }}</code>

                            <p class="small text-muted mb-3">Luego ingresa el código de 6 dígitos que genera tu app:</p>
                        </div>

                        <form action="{{ route('two-factor.confirmar') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-8 mx-auto text-center">
                                <label for="codigo_confirmar" class="form-label" style="font-size:13px; font-weight:500;">
                                    Código de 6 dígitos
                                </label>
                                <input type="text" name="codigo" id="codigo_confirmar" class="form-control form-control-lg text-center"
                                       placeholder="000000" required pattern="\d{6}" maxlength="6"
                                       style="font-size:1.5rem; letter-spacing:8px;">
                                <button type="submit" class="btn btn-primary btn-lg mt-3 w-100">
                                    <i class="fas fa-shield-halved me-2"></i>Confirmar y Activar 2FA
                                </button>
                            </div>
                        </form>

                    @else
                        {{-- PASO 1: Generar secreto --}}
                        <h5 class="fw-bold mb-4">Protege tu cuenta con 2FA</h5>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            La verificación en dos pasos agrega una capa extra de seguridad. Después de tu contraseña, necesitarás un código de 6 dígitos generado por tu aplicación de autenticación.
                        </div>

                        <ul class="mb-4">
                            <li>Descarga <strong>Google Authenticator</strong> (Play Store / App Store) o <strong>Authy</strong></li>
                            <li>Escanea el código QR o ingresa el secreto</li>
                            <li>Confirma el código generado para activar</li>
                        </ul>

                        <div class="text-center">
                            <button type="button" class="btn btn-primary btn-lg" onclick="generarSecreto()">
                                <i class="fas fa-qrcode me-2"></i>Generar Código QR
                            </button>
                        </div>

                        <div id="setup-container" class="d-none mt-4"></div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function generarSecreto() {
        const btn = event.currentTarget;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando...';

        try {
            const response = await fetch('{{ route("two-factor.generar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            const container = document.getElementById('setup-container');
            container.classList.remove('d-none');
            container.innerHTML = `
                <h5 class="fw-bold mb-4">Paso 1: Escanea el código QR</h5>
                <div class="text-center mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(data.qr_url)}"
                         alt="QR Code" class="img-thumbnail" style="width:220px;height:220px;">
                </div>
                <p class="text-center mb-1"><strong>O ingresa manualmente:</strong></p>
                <p class="text-center mb-3"><code class="bg-light p-2 rounded">${data.secret}</code></p>
                <p class="text-center small text-muted mb-3">Luego ingresa el código de 6 dígitos:</p>
                <form action="{{ route('two-factor.confirmar') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-8 mx-auto text-center">
                        <input type="text" name="codigo" class="form-control form-control-lg text-center"
                               placeholder="000000" required pattern="\\d{6}" maxlength="6"
                               style="font-size:1.5rem; letter-spacing:8px;">
                        <button type="submit" class="btn btn-primary btn-lg mt-3 w-100">
                            <i class="fas fa-shield-halved me-2"></i>Confirmar y Activar 2FA
                        </button>
                    </div>
                </form>
            `;
        } catch (error) {
            alert('Error al generar el secreto. Intenta nuevamente.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-qrcode me-2"></i>Generar Código QR';
        }
    }
</script>
@endpush