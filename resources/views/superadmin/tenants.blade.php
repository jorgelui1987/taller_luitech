<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin - Tenants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('superadmin.dashboard') }}">🔐 SuperAdmin CRM</a>
            <div class="ms-auto">
                <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-success btn-sm me-2">+ Nuevo Tenant</a>
                <a href="{{ route('superadmin.logout') }}" class="btn btn-outline-light btn-sm"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                <form id="logout-form" action="{{ route('superadmin.logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2>Gestión de Tenants</h2>

        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif

        <div class="card mt-3">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Subdominio</th>
                            <th>Plan</th>
                            <th>Usuarios</th>
                            <th>Estado</th>
                            <th>Expira</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->id }}</td>
                            <td>{{ $tenant->empresa }}</td>
                            <td><code>{{ $tenant->subdominio }}</code></td>
                            <td><span class="badge bg-info">{{ $tenant->plan }}</span></td>
                            <td>{{ $tenant->usuarios_count ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $tenant->estado === 'activo' ? 'success' : ($tenant->estado === 'suspendido' ? 'warning' : 'danger') }}">
                                    {{ $tenant->estado }}
                                </span>
                            </td>
                            <td>
                                @if($tenant->fecha_expiracion)
                                    {{ $tenant->fecha_expiracion->format('d/m/Y') }}
                                    @if($tenant->fecha_expiracion->isPast())
                                        <span class="badge bg-danger">Vencido</span>
                                    @endif
                                @else
                                    Sin fecha
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="btn btn-sm btn-primary">Editar</a>
                                <a href="{{ route('superadmin.tenants.users', $tenant) }}" class="btn btn-sm btn-secondary" title="Usuarios">👤</a>
                                <a href="{{ route('superadmin.tenants.login-as', $tenant) }}" class="btn btn-sm btn-info"
                                   onclick="return confirm('¿Iniciar sesión como admin de {{ $tenant->empresa }}?')"
                                   title="Login como tenant">🔑</a>

                                <form action="{{ route('superadmin.tenants.toggle', $tenant) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-{{ $tenant->estado === 'activo' ? 'warning' : 'success' }}"
                                            onclick="return confirm('¿{{ $tenant->estado === 'activo' ? 'Suspender' : 'Activar' }} este tenant?')">
                                        {{ $tenant->estado === 'activo' ? 'Suspender' : 'Activar' }}
                                    </button>
                                </form>

                                <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Eliminar PERMANENTEMENTE este tenant? Todos sus datos se perderán.')">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">No hay tenants registrados</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $tenants->links() }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>