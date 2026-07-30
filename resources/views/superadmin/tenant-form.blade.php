<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin - {{ isset($tenant) ? 'Editar' : 'Nuevo' }} Tenant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('superadmin.dashboard') }}">🔐 SuperAdmin CRM</a>
            <div class="ms-auto">
                <a href="{{ route('superadmin.tenants') }}" class="btn btn-outline-light btn-sm">← Volver</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2>{{ isset($tenant) ? 'Editar Tenant: ' . $tenant->empresa : 'Crear Nuevo Tenant' }}</h2>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($tenant) ? route('superadmin.tenants.update', $tenant) : route('superadmin.tenants.store') }}" class="mt-3">
            @csrf
            @if(isset($tenant)) @method('PUT') @endif

            <div class="card">
                <div class="card-header">Datos del Tenant</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre de empresa *</label>
                            <input type="text" name="empresa" class="form-control" value="{{ old('empresa', $tenant->empresa ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subdominio *</label>
                            <input type="text" name="subdominio" class="form-control" value="{{ old('subdominio', $tenant->subdominio ?? '') }}" required placeholder="mitienda">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email contacto *</label>
                            <input type="email" name="email_contacto" class="form-control" value="{{ old('email_contacto', $tenant->email_contacto ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono contacto</label>
                            <input type="text" name="telefono_contacto" class="form-control" value="{{ old('telefono_contacto', $tenant->telefono_contacto ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Plan *</label>
                            <select name="plan" class="form-select" id="planSelect" required onchange="actualizarLimites()">
                                @foreach(['gratis'=>'🌱 Gratis (3 usuarios, 50 prod.)','basico'=>'🚀 Básico (5 usuarios, 200 prod.)','profesional'=>'⭐ Profesional (15 usuarios, 1000 prod.)','empresarial'=>'🏢 Empresarial (Ilimitado)'] as $val => $label)
                                    <option value="{{ $val }}" {{ (old('plan', $tenant->plan ?? '') === $val) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Máx. Usuarios (auto según plan)</label>
                            <input type="text" class="form-control" id="maxUsuariosDisplay" value="{{ $limitesPorPlan[$tenant->plan ?? 'gratis']['max_usuarios'] ?? 3 }}" readonly style="background:#f0f0f0; font-weight:bold;">
                            <input type="hidden" name="max_usuarios" id="maxUsuariosHidden" value="{{ $limitesPorPlan[$tenant->plan ?? 'gratis']['max_usuarios'] ?? 3 }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Máx. Productos (auto según plan)</label>
                            <input type="text" class="form-control" id="maxProductosDisplay" value="{{ $limitesPorPlan[$tenant->plan ?? 'gratis']['max_productos'] ?? 50 }}" readonly style="background:#f0f0f0; font-weight:bold;">
                            <input type="hidden" name="max_productos" id="maxProductosHidden" value="{{ $limitesPorPlan[$tenant->plan ?? 'gratis']['max_productos'] ?? 50 }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha expiración</label>
                            <input type="date" name="fecha_expiracion" class="form-control" value="{{ old('fecha_expiracion', isset($tenant) && $tenant->fecha_expiracion ? $tenant->fecha_expiracion->format('Y-m-d') : '') }}">
                        </div>
                        @if(isset($tenant))
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado *</label>
                            <select name="estado" class="form-select" required>
                                @foreach(['activo','suspendido','cancelado'] as $est)
                                    <option value="{{ $est }}" {{ $tenant->estado === $est ? 'selected' : '' }}>{{ ucfirst($est) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(!isset($tenant))
            <div class="card mt-3">
                <div class="card-header">Usuario Administrador</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre_admin" class="form-control" value="{{ old('nombre_admin') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email_admin" class="form-control" value="{{ old('email_admin') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" name="password_admin" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Confirmar contraseña *</label>
                            <input type="password" name="password_admin_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary mt-3">
                {{ isset($tenant) ? 'Actualizar Tenant' : 'Crear Tenant' }}
            </button>
        </form>
    </div>

    <script>
    const limitesPorPlan = {
        'gratis':       { usuarios: 3,  productos: 50 },
        'basico':       { usuarios: 5,  productos: 200 },
        'profesional':  { usuarios: 15, productos: 1000 },
        'empresarial':  { usuarios: 999, productos: 99999 },
    };

    function actualizarLimites() {
        const plan = document.getElementById('planSelect').value;
        const limites = limitesPorPlan[plan] || limitesPorPlan['gratis'];
        document.getElementById('maxUsuariosDisplay').value = limites.usuarios;
        document.getElementById('maxUsuariosHidden').value = limites.usuarios;
        document.getElementById('maxProductosDisplay').value = limites.productos;
        document.getElementById('maxProductosHidden').value = limites.productos;
    }
    </script>
</body>
</html>
