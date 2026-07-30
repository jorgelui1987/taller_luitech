<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin - Precios de Planes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">🔐 SuperAdmin CRM</a>
            <div class="ms-auto">
                <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="{{ route('superadmin.tenants') }}" class="btn btn-outline-light btn-sm me-2">Tenants</a>
                <a href="{{ route('superadmin.planes-precios') }}" class="btn btn-light btn-sm me-2">Precios</a>
                <a href="{{ route('superadmin.logout') }}" class="btn btn-outline-light btn-sm"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                <form id="logout-form" action="{{ route('superadmin.logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-currency-dollar me-2"></i>Precios de Planes</h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            @foreach($planes as $plan)
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('superadmin.planes-precios.update', $plan) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="text-center mb-3">
                                @php
                                    $iconos = [
                                        'gratis' => '🌱',
                                        'basico' => '🚀',
                                        'profesional' => '⭐',
                                        'empresarial' => '🏢',
                                    ];
                                    $colores = [
                                        'gratis' => '#6b7280',
                                        'basico' => '#06b6d4',
                                        'profesional' => '#7c3aed',
                                        'empresarial' => '#1e1b4b',
                                    ];
                                @endphp
                                <span style="font-size:2rem;">{{ $iconos[$plan->plan_key] ?? '📋' }}</span>
                                <h5 class="fw-bold mt-2" style="color:{{ $colores[$plan->plan_key] ?? '#333' }};">
                                    {{ $plan->nombre }}
                                </h5>
                                <span class="badge bg-{{ $plan->activo ? 'success' : 'secondary' }}">
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio Mensual</label>
                                <div class="input-group">
                                    <input type="text" name="simbolo" class="form-control" style="max-width:60px;"
                                           value="{{ $plan->simbolo }}" required>
                                    <input type="number" name="precio_mensual" class="form-control"
                                           value="{{ $plan->precio_mensual }}" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Moneda</label>
                                <select name="moneda" class="form-select" required>
                                    <option value="PEN" {{ $plan->moneda == 'PEN' ? 'selected' : '' }}>PEN (S/.)</option>
                                    <option value="USD" {{ $plan->moneda == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="CLP" {{ $plan->moneda == 'CLP' ? 'selected' : '' }}>CLP ($)</option>
                                    <option value="MXN" {{ $plan->moneda == 'MXN' ? 'selected' : '' }}>MXN ($)</option>
                                    <option value="COP" {{ $plan->moneda == 'COP' ? 'selected' : '' }}>COP ($)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <input type="text" name="descripcion" class="form-control"
                                       value="{{ $plan->descripcion }}" maxlength="255">
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" name="activo" class="form-check-input" id="activo_{{ $plan->id }}"
                                       value="1" {{ $plan->activo ? 'checked' : '' }}>
                                <label class="form-check-label" for="activo_{{ $plan->id }}">Plan activo</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save me-2"></i>Guardar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Información</h6>
                <p class="text-muted mb-0" style="font-size:13px;">
                    Los precios se actualizan automáticamente en la landing page y en el formulario de registro.
                    Los cambios se reflejan inmediatamente después de guardar.
                </p>
            </div>
        </div>
    </div>
</body>
</html>