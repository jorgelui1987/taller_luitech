<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin - Usuarios de {{ $tenant->empresa }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('superadmin.dashboard') }}">🔐 SuperAdmin CRM</a>
            <div class="ms-auto">
                <a href="{{ route('superadmin.tenants') }}" class="btn btn-outline-light btn-sm">← Volver a Tenants</a>
                <a href="{{ route('superadmin.logout') }}" class="btn btn-outline-light btn-sm ms-2"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                <form id="logout-form" action="{{ route('superadmin.logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Usuarios de {{ $tenant->empresa }}</h2>
                <p class="text-muted">Gestiona contraseñas de los usuarios de este tenant</p>
            </div>
            <span class="badge bg-info" style="font-size:1rem;">{{ $usuarios->count() }} usuarios</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td><code>{{ $usuario->email }}</code></td>
                            <td>
                                <span class="badge bg-{{ $usuario->rol === 'admin' ? 'primary' : ($usuario->rol === 'vendedor' ? 'info' : 'warning') }}">
                                    {{ $usuario->rol }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $usuario->activo ? 'success' : 'danger' }}">
                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <!-- Botón cambiar contraseña -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalPassword"
                                        onclick="document.getElementById('formCambiarPass').action = '{{ route('superadmin.usuarios.change-password', $usuario) }}';
                                                document.getElementById('userNamePass').textContent = '{{ addslashes($usuario->name) }}';
                                                document.getElementById('userEmailPass').textContent = '{{ $usuario->email }}';">
                                    🔑 Cambiar contraseña
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">No hay usuarios en este tenant</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    <a href="{{ route('superadmin.tenants.login-as', $tenant) }}" class="btn btn-info"
                       onclick="return confirm('¿Iniciar sesión como admin de {{ $tenant->empresa }}?')">
                        🔑 Iniciar sesión como admin
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="modalPassword" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🔑 Cambiar contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCambiarPass" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Usuario: <strong id="userNamePass"></strong> (<span id="userEmailPass"></span>)</p>
                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña *</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar contraseña *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Cambiar contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>