<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuperAdmin - Backups</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('superadmin.dashboard') }}">🔐 SuperAdmin CRM</a>
            <div class="ms-auto">
                <a href="{{ route('superadmin.tenants') }}" class="btn btn-outline-light btn-sm me-2">Tenants</a>
                <a href="{{ route('superadmin.planes-precios') }}" class="btn btn-outline-light btn-sm me-2">Precios</a>
                <a href="{{ route('superadmin.backups') }}" class="btn btn-light btn-sm me-2">Backups</a>
                <a href="{{ route('superadmin.logout') }}" class="btn btn-outline-light btn-sm"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar Sesión</a>
                <form id="logout-form" action="{{ route('superadmin.logout') }}" method="POST" class="d-none">@csrf</form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2>Backups del Sistema</h2>
        <p class="text-muted">
            Copias de seguridad automáticas (diarias a las 2:00 AM, se conservan 7 días)
            y manuales. Incluyen los datos de <strong>todas las empresas</strong>.
            Desde aquí solo puedes <strong>descargarlos</strong>; la restauración se realiza
            desde el panel de cada empresa (Backups → Restaurar).
        </p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Archivos disponibles ({{ count($backups) }})</span>
                <span class="badge bg-secondary">{{ number_format(array_sum(array_column($backups, 'tamanio')) / 1048576, 2) }} MB en total</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Tipo</th>
                            <th>Tamaño</th>
                            <th>Fecha</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                        <tr>
                            <td class="font-monospace small">{{ $backup['nombre'] }}</td>
                            <td>
                                @if(str_starts_with($backup['nombre'], 'backup_auto_'))
                                    <span class="badge bg-primary">Automático</span>
                                @elseif(str_starts_with($backup['nombre'], 'pre_reset_'))
                                    <span class="badge bg-warning text-dark">Pre-Reset</span>
                                @elseif(str_starts_with($backup['nombre'], 'pre_restore_'))
                                    <span class="badge bg-warning text-dark">Pre-Restaurar</span>
                                @else
                                    <span class="badge bg-success">Manual</span>
                                @endif
                            </td>
                            <td>{{ number_format($backup['tamanio'] / 1024, 1) }} KB</td>
                            <td>{{ $backup['fecha']->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('superadmin.backups.descargar', $backup['nombre']) }}"
                                   class="btn btn-sm btn-outline-primary">⬇ Descargar</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No hay backups todavía. Se generará uno automáticamente hoy a las 2:00 AM,
                                o puedes crear uno desde el panel de una empresa (Backups → Crear backup).
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <strong>💡 Recomendación:</strong> descarga el último backup a tu computadora al menos
            una vez por semana. Los archivos viven solo en el servidor; si el servidor falla,
            solo podrás recuperar lo que hayas descargado.
        </div>
    </div>
</body>
</html>