@extends('layouts.app')
@section('title', 'Garantías')

@section('breadcrumb')
    <ul><li class="breadcrumb-item active">Garantías</li></ul>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1e1b4b;"><i class="fas fa-shield-alt me-2" style="color:#10b981;"></i>Garantías</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Registro de productos que ingresan por garantía</p>
    </div>
    <a href="{{ route('garantias.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Nueva Garantía
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <label for="buscar" class="visually-hidden">Buscar</label>
                <input type="text" id="buscar" class="form-control" placeholder="🔍 Buscar por N° garantía, venta o cliente..."
                       value="{{ request('buscar') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label for="estado" class="visually-hidden">Estado</label>
                <select name="estado" id="estado" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="completada" {{ request('estado')=='completada'?'selected':'' }}>Completada</option>
                    <option value="anulada" {{ request('estado')=='anulada'?'selected':'' }}>Anulada</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="fecha_desde" class="visually-hidden">Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label for="fecha_hasta" class="visually-hidden">Hasta</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('garantias.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i>Limpiar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Garantía</th>
                        <th>Venta</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Registrado por</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($garantias as $g)
                    <tr>
                        <td style="font-weight:600;">{{ $g->numero_garantia }}</td>
                        <td>
                            <a href="{{ route('ventas.show', $g->venta) }}" class="text-decoration-none">{{ $g->venta->numero_venta ?? '—' }}</a>
                        </td>
                        <td>{{ $g->cliente->nombre_completo ?? 'Venta general' }}</td>
                        <td>{{ $g->fecha_garantia->format('d/m/Y H:i') }}</td>
                        <td>{{ $g->usuario->name ?? '—' }}</td>
                        <td>
                            @if($g->estado === 'completada')
                                <span class="badge" style="background:#d1fae5;color:#065f46;">Completada</span>
                            @else
                                <span class="badge" style="background:#fee2e2;color:#991b1b;">Anulada</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('garantias.show', $g) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-shield-alt" style="font-size:40px;color:#e5e7eb;display:block;margin-bottom:8px;"></i>
                            No hay garantías registradas
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $garantias->appends(request()->query())->links() }}
</div>
@endsection