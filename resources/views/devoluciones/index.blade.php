@extends('layouts.app')
@section('title', 'Devoluciones')

@section('breadcrumb')
    <ul><li class="breadcrumb-item active">Devoluciones</li></ul>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-undo-alt me-2" style="color:#a855f7;"></i>Devoluciones</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Total del mes: <strong style="color:#a855f7;">{{ formatoMoneda($totalMes) }}</strong>
        </p>
    </div>
    <a href="{{ route('devoluciones.create') }}" class="btn btn-primary px-4">
        <i class="fas fa-plus me-2"></i>Nueva Devolución
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

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="buscar" class="visually-hidden">Buscar devolución</label>
                <input type="text" class="form-control" name="buscar" id="buscar"
                       placeholder="Buscar devolución, venta o cliente..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <label for="estado" class="visually-hidden">Filtrar por estado</label>
                <select class="form-select" name="estado" id="estado">
                    <option value="">Todos los estados</option>
                    <option value="completada" {{ request('estado')=='completada'?'selected':'' }}>Completada</option>
                    <option value="anulada"    {{ request('estado')=='anulada'?'selected':'' }}>Anulada</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="tipo" class="visually-hidden">Filtrar por tipo</label>
                <select class="form-select" name="tipo" id="tipo">
                    <option value="">Todos los tipos</option>
                    <option value="devolucion" {{ request('tipo')=='devolucion'?'selected':'' }}>Devolución</option>
                    <option value="garantia"   {{ request('tipo')=='garantia'?'selected':'' }}>Garantía</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="fecha_desde" class="visually-hidden">Fecha desde</label>
                <input type="date" class="form-control" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-2">
                <label for="fecha_hasta" class="visually-hidden">Fecha hasta</label>
                <input type="date" class="form-control" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    <i class="fas fa-filter me-1"></i>
                </button>
                <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">N° Devolución</th>
                        <th>Venta</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Motivo</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devoluciones as $devolucion)
                    <tr>
                        <td class="ps-4">
                            <span style="font-weight:600; color:#a855f7;">{{ $devolucion->numero_devolucion }}</span>
                        </td>
                        <td style="font-size:13px;">
                            <a href="{{ route('ventas.show', $devolucion->venta) }}" class="text-decoration-none">
                                {{ $devolucion->venta->numero_venta ?? '—' }}
                            </a>
                        </td>
                        <td style="font-size:13px;">
                            <div>{{ $devolucion->cliente?->nombre_completo ?? 'Venta general' }}</div>
                            <div style="font-size:11px; color:#9ca3af;">{{ $devolucion->usuario->name ?? '' }}</div>
                        </td>
                        <td style="font-size:12px;">
                            <div>{{ $devolucion->fecha_devolucion->format('d/m/Y') }}</div>
                            <div style="color:#9ca3af;">{{ $devolucion->fecha_devolucion->format('H:i') }}</div>
                        </td>
                        <td>
                            @php
                                $tipoCfg = [
                                    'devolucion' => ['icon'=>'🔄', 'label'=>'Devolución', 'bg'=>'#e0f2fe', 'color'=>'#0369a1'],
                                    'garantia'   => ['icon'=>'🛡️', 'label'=>'Garantía', 'bg'=>'#d1fae5', 'color'=>'#065f46'],
                                ];
                                $t = $tipoCfg[$devolucion->tipo] ?? ['icon'=>'📦', 'label'=>ucfirst($devolucion->tipo), 'bg'=>'#f3f4f6', 'color'=>'#374151'];
                            @endphp
                            <span style="background:{{ $t['bg'] }}; color:{{ $t['color'] }};
                                border-radius:20px; padding:4px 10px; font-size:11px; font-weight:500;">
                                {{ $t['icon'] }} {{ $t['label'] }}
                            </span>
                        </td>
                        <td style="font-size:12px; color:#6b7280;">
                            @php
                                $motivos = [
                                    'garantia'       => '🛡️ Garantía',
                                    'defecto'        => '⚠️ Defecto',
                                    'cambio_opinion' => '💭 Cambio de opinión',
                                    'error_venta'    => '❌ Error de venta',
                                    'otro'           => '📦 Otro',
                                ];
                            @endphp
                            {{ $motivos[$devolucion->motivo] ?? ucfirst($devolucion->motivo) }}
                        </td>
                        <td style="font-weight:700; color:#1e1b4b;">
                            {{ formatoMoneda($devolucion->total) }}
                        </td>
                        <td>
                            @if($devolucion->estado === 'anulada')
                                <span style="background:#fee2e2; color:#991b1b;
                                    border-radius:20px; padding:4px 10px; font-size:11px; font-weight:500;">
                                    Anulada
                                </span>
                            @else
                                <span style="background:#d1fae5; color:#065f46;
                                    border-radius:20px; padding:4px 10px; font-size:11px; font-weight:500;">
                                    Completada
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('devoluciones.show', $devolucion) }}"
                                   class="btn btn-sm" style="background:#ede9fe; color:#7c3aed; border-radius:8px; padding:5px 10px;">
                                    <i class="fas fa-eye fa-sm"></i>
                                </a>
                                @if($devolucion->estado === 'completada')
                                <form action="{{ route('devoluciones.anular', $devolucion) }}" method="POST"
                                      onsubmit="return confirm('¿Anular esta devolución? Se restará el stock devuelto.')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm"
                                            style="background:#fee2e2; color:#dc2626; border-radius:8px; padding:5px 10px;">
                                        <i class="fas fa-ban fa-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-undo-alt fa-3x mb-3 d-block" style="color:#d1d5db;"></i>
                            <p class="text-muted mb-2">No hay devoluciones registradas</p>
                            <a href="{{ route('devoluciones.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Registrar primera devolución
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devoluciones->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted" style="font-size:13px;">
                Mostrando {{ $devoluciones->firstItem() }}–{{ $devoluciones->lastItem() }} de {{ $devoluciones->total() }} devoluciones
            </span>
            {{ $devoluciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
