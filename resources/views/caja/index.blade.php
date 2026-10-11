@extends('layouts.app')

@section('title', 'Cierre de Caja')
@section('breadcrumb')
    <ul><li class="breadcrumb-item active" aria-current="page">Cierre de Caja</li></ul>
@endsection

@push('styles')
<style>
    .ui-kpi .caja-value { font-size: 20px; font-weight: 800; color:#0f172a; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight:600;">
        <i class="fas fa-cash-register me-2" style="color:var(--accent1);"></i>
        Cierre de Caja
    </h4>
    <div class="d-flex gap-2">
        @if($cajaAbierta)
            <a href="{{ route('caja.cerrar') }}" class="btn btn-danger px-3">
                <i class="fas fa-lock me-1"></i>Cerrar Caja
            </a>
        @else
            <a href="{{ route('caja.abrir') }}" class="btn ui-btn-brand px-3">
                <i class="fas fa-unlock me-1"></i>Abrir Caja
            </a>
        @endif
    </div>
</div>

{{-- ── Estado de caja actual ── --}}
<div class="row g-2 mb-3">
    @if($cajaAbierta)
    <div class="col-6 col-xl-3">
        <div class="ui-kpi" style="border-left:5px solid #10b981;">
            <div class="l">CAJA ACTUAL</div>
            <div class="v" style="font-size:18px;">Abierta</div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">{{ $cajaAbierta->fecha_apertura?->format('d/m/Y H:i') }}</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ui-kpi" style="border-left:5px solid #06b6d4;">
            <div class="l">MONTO INICIAL</div>
            <div class="v" style="font-size:18px;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($cajaAbierta->monto_inicial, 2) }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">Apertura del día</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ui-kpi" style="border-left:5px solid #8b5cf6;">
            <div class="l">VENTAS DEL DÍA</div>
            <div class="v" style="font-size:18px;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($totalVentasHoy + $totalReparacionesHoy, 2) }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">Ventas + reparaciones</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="ui-kpi" style="border-left:5px solid #f59e0b;">
            <div class="l">CAJERO</div>
            <div class="v" style="font-size:18px;">{{ $cajaAbierta->usuario->name ?? '—' }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">Responsable actual</div>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="ui-card">
            <div class="text-center py-5">
                <i class="fas fa-cash-register" style="font-size:48px; color:var(--text-muted); opacity:.4; display:block; margin-bottom:12px;"></i>
                <h5 class="mb-2" style="font-weight:600;">No hay caja abierta</h5>
                <p class="mb-3" style="color:var(--text-muted); font-size:13px;">
                    Abre la caja para comenzar a registrar tus ventas del día.
                </p>
                <a href="{{ route('caja.abrir') }}" class="btn ui-btn-brand px-4">
                    <i class="fas fa-unlock me-1"></i>Abrir Caja
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ── Filtros ── --}}
<div class="ui-card mb-3">
    <div class="p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="abierta" {{ request('estado') == 'abierta' ? 'selected' : '' }}>Abiertas</option>
                    <option value="cerrada" {{ request('estado') == 'cerrada' ? 'selected' : '' }}>Cerradas</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1">Cajero</label>
                <select name="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('usuario_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <a href="{{ route('caja.index') }}" class="btn btn-sm btn-outline-secondary px-3">
                    <i class="fas fa-eraser me-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Historial de cierres ── --}}
<div class="ui-card">
    <div class="p-3">
        <h6 class="mb-3" style="font-size:15px; font-weight:600; color:var(--text-dark);">
            <i class="fas fa-history me-2" style="color:var(--accent1);"></i>
            Historial de Cierres
        </h6>
        <div class="table-responsive">
            <table class="table table-sm ui-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cajero</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th class="text-end">Monto Inicial</th>
                        <th class="text-end">Total Esperado</th>
                        <th class="text-end">Total Contado</th>
                        <th class="text-end">Diferencia</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cierres as $cierre)
                        <tr>
                            <td>#CC-{{ str_pad($cierre->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $cierre->usuario->name ?? '—' }}</td>
                            <td>{{ $cierre->fecha_apertura?->format('d/m/Y H:i') }}</td>
                            <td>{{ $cierre->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-end">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($cierre->monto_inicial, 2) }}</td>
                            <td class="text-end">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($cierre->total_esperado, 2) }}</td>
                            <td class="text-end">{{ $cierre->total_contado !== null ? ($empresa->simbolo_moneda ?? '$') . ' ' . number_format($cierre->total_contado, 2) : '—' }}</td>
                            <td class="text-end {{ $cierre->diferencia > 0 ? 'text-success' : ($cierre->diferencia < 0 ? 'text-danger' : 'text-muted') }}">
                                {{ $cierre->diferencia > 0 ? '+' : '' }}{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($cierre->diferencia, 2) }}
                            </td>
                            <td class="text-center">
                                @if($cierre->estado == 'abierta')
                                    <span class="ui-badge ui-badge-green">Abierta</span>
                                @else
                                    <span class="ui-badge ui-badge-gray">Cerrada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('caja.show', $cierre) }}" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($cierre->estado == 'cerrada')
                                <a href="{{ route('caja.ticket', $cierre) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Imprimir ticket">
                                    <i class="fas fa-print"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4" style="color:var(--text-muted);">
                                <i class="fas fa-inbox mb-2" style="font-size:28px; display:block; opacity:.4;"></i>
                                No hay cierres de caja registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $cierres->links() }}
        </div>
    </div>
</div>
@endsection