@extends('layouts.app')

@section('title', 'Cierre de Caja')
@section('breadcrumb')
    <ul><li class="breadcrumb-item active" aria-current="page">Cierre de Caja</li></ul>
@endsection

@push('styles')
<style>
    .caja-card {
        border-radius: 16px;
        padding: 20px;
        position: relative;
        overflow: hidden;
    }
    .caja-card .caja-icon {
        width: 44px; height: 44px;
        background: rgba(255,255,255,.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
    }
    .caja-card .caja-value { font-size: 24px; font-weight: 700; line-height: 1; margin-bottom: 4px; }
    .caja-card .caja-label { font-size: 12px; opacity: .85; margin-bottom: 6px; }
    .bg-grad-purple { background: linear-gradient(135deg, #a855f7, #7c3aed); }
    .bg-grad-green  { background: linear-gradient(135deg, #10b981, #059669); }
    .bg-grad-cyan   { background: linear-gradient(135deg, #06b6d4, #0284c7); }
    .bg-grad-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
    .bg-grad-pink   { background: linear-gradient(135deg, #ec4899, #db2777); }
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
            <a href="{{ route('caja.abrir') }}" class="btn btn-primary px-3">
                <i class="fas fa-unlock me-1"></i>Abrir Caja
            </a>
        @endif
    </div>
</div>

{{-- ── Estado de caja actual ── --}}
<div class="row g-3 mb-4">
    @if($cajaAbierta)
    <div class="col-md-3">
        <div class="caja-card bg-grad-green text-white">
            <div class="caja-icon"><i class="fas fa-unlock"></i></div>
            <div class="caja-label">Caja Actual</div>
            <div class="caja-value" style="font-size:16px;">Abierta</div>
            <div style="font-size:11px; opacity:.8;">{{ $cajaAbierta->fecha_apertura?->format('d/m/Y H:i') }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="caja-card bg-grad-cyan text-white">
            <div class="caja-icon"><i class="fas fa-coins"></i></div>
            <div class="caja-label">Monto Inicial</div>
            <div class="caja-value" style="font-size:18px;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($cajaAbierta->monto_inicial, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="caja-card bg-grad-purple text-white">
            <div class="caja-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="caja-label">Ventas del Día</div>
            <div class="caja-value" style="font-size:18px;">{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($totalVentasHoy + $totalReparacionesHoy, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="caja-card bg-grad-orange text-white">
            <div class="caja-icon"><i class="fas fa-user"></i></div>
            <div class="caja-label">Cajero</div>
            <div class="caja-value" style="font-size:16px;">{{ $cajaAbierta->usuario->name ?? '—' }}</div>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-cash-register" style="font-size:48px; color:var(--text-muted); opacity:.4; display:block; margin-bottom:12px;"></i>
                <h5 class="mb-2" style="font-weight:600;">No hay caja abierta</h5>
                <p class="mb-3" style="color:var(--text-muted); font-size:13px;">
                    Abre la caja para comenzar a registrar tus ventas del día.
                </p>
                <a href="{{ route('caja.abrir') }}" class="btn btn-primary px-4">
                    <i class="fas fa-unlock me-1"></i>Abrir Caja
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ── Filtros ── --}}
<div class="card mb-4">
    <div class="card-body">
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
<div class="card">
    <div class="card-body">
        <h6 class="mb-3" style="font-size:15px; font-weight:600; color:var(--text-dark);">
            <i class="fas fa-history me-2" style="color:var(--accent1);"></i>
            Historial de Cierres
        </h6>
        <div class="table-responsive">
            <table class="table table-sm">
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
                            <td class="text-end">{{ $cierre->total_contado !== null ? '{{ $empresa->simbolo_moneda ?? '$' }} ' . number_format($cierre->total_contado, 2) : '—' }}</td>
                            <td class="text-end {{ $cierre->diferencia > 0 ? 'text-success' : ($cierre->diferencia < 0 ? 'text-danger' : 'text-muted') }}">
                                {{ $cierre->diferencia > 0 ? '+' : '' }}{{ $empresa->simbolo_moneda ?? '$' }} {{ number_format($cierre->diferencia, 2) }}
                            </td>
                            <td class="text-center">
                                @if($cierre->estado == 'abierta')
                                    <span class="badge bg-success">Abierta</span>
                                @else
                                    <span class="badge bg-secondary">Cerrada</span>
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