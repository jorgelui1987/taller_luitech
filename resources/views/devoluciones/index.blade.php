@extends('layouts.app')
@section('title', 'Devoluciones')

@section('breadcrumb')
    <ul><li class="breadcrumb-item active">Devoluciones</li></ul>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-undo-alt me-2" style="color:#0891b2;"></i>Devoluciones</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Total del mes: <strong style="color:#0891b2;">{{ formatoMoneda($totalMes) }}</strong>
        </p>
    </div>
    <a href="{{ route('devoluciones.create') }}" class="btn ui-btn-brand px-4">
        <i class="fas fa-plus me-2"></i>Nueva Devolución
    </a>
</div>

{{-- Tarjetas de resumen estilo dashboard --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-4">
        <div class="ui-kpi d-flex align-items-center gap-3" style="border-left:5px solid #0284c7;">
            <div style="width:44px;height:44px;border-radius:12px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;color:#0369a1;flex:none;">
                <i class="fas fa-undo-alt"></i>
            </div>
            <div>
                <div style="font-size:11px;color:#9ca3af;">DEVOLUCIONES DEL MES</div>
                <div style="font-size:22px;font-weight:700;color:#0f172a;">{{ $totalCompletadas }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('devoluciones.index', ['condicion' => 'daniado']) }}" class="text-decoration-none">
            <div class="ui-kpi d-flex align-items-center gap-3" style="border-left:5px solid #dc2626;{{ request('condicion')=='daniado' ? 'box-shadow:0 0 0 2px #dc2626;' : '' }}">
                <div style="width:44px;height:44px;border-radius:12px;background:#fee2e2;display:flex;align-items:center;justify-content:center;color:#dc2626;flex:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <div style="font-size:11px;color:#9ca3af;">CON DAÑADOS</div>
                    <div style="font-size:22px;font-weight:700;color:#dc2626;">{{ $totalDaniadas }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-4">
        <div class="ui-kpi d-flex align-items-center gap-3" style="border-left:5px solid #059669;">
            <div style="width:44px;height:44px;border-radius:12px;background:#d1fae5;display:flex;align-items:center;justify-content:center;color:#059669;flex:none;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <div style="font-size:11px;color:#9ca3af;">TOTAL DEVUELTO DEL MES</div>
                <div style="font-size:22px;font-weight:700;color:#059669;">{{ formatoMoneda($totalMes) }}</div>
            </div>
        </div>
    </div>
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
<div class="ui-card mb-3">
    <div class="p-3">
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
                <label for="condicion" class="visually-hidden">Filtrar por condición</label>
                <select class="form-select" name="condicion" id="condicion">
                    <option value="">Todas las condiciones</option>
                    <option value="daniado" {{ request('condicion')=='daniado'?'selected':'' }}>Con dañados</option>
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
                <button type="submit" class="btn ui-btn-brand flex-1">
                    <i class="fas fa-filter me-1"></i>
                </button>
                <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="ui-card">
    <div class="p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 ui-table">
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
                            <span style="font-weight:600; color:#0891b2;">{{ $devolucion->numero_devolucion }}</span>
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
                                    'devolucion' => ['icon'=>'fa-rotate-left', 'label'=>'Devolución', 'badge'=>'ui-badge-blue'],
                                    'garantia'   => ['icon'=>'fa-shield-halved', 'label'=>'Garantía', 'badge'=>'ui-badge-green'],
                                ];
                                $t = $tipoCfg[$devolucion->tipo] ?? ['icon'=>'fa-box', 'label'=>ucfirst($devolucion->tipo), 'badge'=>'ui-badge-gray'];
                            @endphp
                            <span class="ui-badge {{ $t['badge'] }}">
                                <i class="fas {{ $t['icon'] }} me-1"></i>{{ $t['label'] }}
                            </span>
                        </td>
                        <td style="font-size:12px; color:#6b7280;">
                            @php
                                $motivos = [
                                    'garantia'       => 'Garantía',
                                    'defecto'        => 'Defecto',
                                    'cambio_opinion' => 'Cambio de opinión',
                                    'error_venta'    => 'Error de venta',
                                    'otro'           => 'Otro',
                                ];
                            @endphp
                            {{ $motivos[$devolucion->motivo] ?? ucfirst($devolucion->motivo) }}
                        </td>
                        <td style="font-weight:700; color:#0f172a;">
                            {{ formatoMoneda($devolucion->total) }}
                        </td>
                        <td>
                            @if($devolucion->estado === 'anulada')
                                <span class="ui-badge ui-badge-red">
                                    Anulada
                                </span>
                            @else
                                <span class="ui-badge ui-badge-green">
                                    Completada
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('devoluciones.show', $devolucion) }}"
                                   class="btn btn-sm" style="background:#cffafe; color:#0e7490; border-radius:8px; padding:5px 10px;">
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
                            <a href="{{ route('devoluciones.create') }}" class="btn ui-btn-brand btn-sm">
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
