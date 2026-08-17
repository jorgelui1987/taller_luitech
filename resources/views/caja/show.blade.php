@extends('layouts.app')

@section('title', 'Detalle Cierre de Caja')
@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('caja.index') }}">Cierre de Caja</a></li></ul>
    <ul><li class="breadcrumb-item active" aria-current="page">Detalle</li></ul>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight:600;">
        <i class="fas fa-file-invoice me-2" style="color:var(--accent1);"></i>
        Cierre #CC-{{ str_pad($cierre->id, 6, '0', STR_PAD_LEFT) }}
    </h4>
    <div class="d-flex gap-2">
        @if($cierre->estado == 'cerrada')
        <a href="{{ route('caja.ticket', $cierre) }}" target="_blank" class="btn btn-outline-info px-3">
            <i class="fas fa-print me-1"></i>Imprimir Ticket
        </a>
        @endif
        <a href="{{ route('caja.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Información general --}}
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-3" style="font-size:14px; font-weight:600; color:var(--text-dark);">
                    <i class="fas fa-info-circle me-2" style="color:var(--accent1);"></i>
                    Información General
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td style="width:40%; color:var(--text-muted);">Cajero</td>
                                <td style="font-weight:600;">{{ $cierre->usuario->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Apertura</td>
                                <td>{{ $cierre->fecha_apertura?->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Cierre</td>
                                <td>{{ $cierre->fecha_cierre?->format('d/m/Y H:i:s') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Estado</td>
                                <td>
                                    @if($cierre->estado == 'abierta')
                                        <span class="badge bg-success">Abierta</span>
                                    @else
                                        <span class="badge bg-secondary">Cerrada</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Duración</td>
                                <td>
                                    @if($cierre->fecha_apertura && $cierre->fecha_cierre)
                                        {{ $cierre->fecha_apertura->diffInMinutes($cierre->fecha_cierre) }} min
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            @if($cierre->observaciones)
                            <tr>
                                <td style="color:var(--text-muted);">Observaciones</td>
                                <td>{{ $cierre->observaciones }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen de ingresos --}}
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-3" style="font-size:14px; font-weight:600; color:var(--text-dark);">
                    <i class="fas fa-chart-pie me-2" style="color:var(--accent1);"></i>
                    Resumen de Montos
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td style="color:var(--text-muted);">Monto Inicial</td>
                                <td class="text-end">{{ $simbolo }} {{ number_format($cierre->monto_inicial, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Ventas Efectivo</td>
                                <td class="text-end">{{ $simbolo }} {{ number_format($cierre->ventas_efectivo, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Ventas Tarjeta</td>
                                <td class="text-end">{{ $simbolo }} {{ number_format($cierre->ventas_tarjeta, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Ventas Transferencia</td>
                                <td class="text-end">{{ $simbolo }} {{ number_format($cierre->ventas_transferencia, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Ventas Otros</td>
                                <td class="text-end">{{ $simbolo }} {{ number_format($cierre->ventas_otros, 2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid var(--text-dark);">
                                <td style="font-weight:700;">Total Ingresos</td>
                                <td class="text-end" style="font-weight:700;">{{ $simbolo }} {{ number_format($cierre->total_ingresos, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">Total Egresos</td>
                                <td class="text-end text-danger">-{{ $simbolo }} {{ number_format($cierre->total_egresos, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Cuadre --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3" style="font-size:14px; font-weight:600; color:var(--text-dark);">
                    <i class="fas fa-balance-scale me-2" style="color:var(--accent1);"></i>
                    Cuadre de Caja
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <div style="font-size:12px; color:var(--text-muted);">Total Esperado</div>
                                <div style="font-size:24px; font-weight:700;">{{ $simbolo }} {{ number_format($cierre->total_esperado, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <div style="font-size:12px; color:var(--text-muted);">Total Contado</div>
                                <div style="font-size:24px; font-weight:700;">
                                    {{ $cierre->total_contado !== null ? $simbolo . ' ' . number_format($cierre->total_contado, 2) : '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card {{ $cierre->diferencia > 0 ? 'bg-success' : ($cierre->diferencia < 0 ? 'bg-danger' : 'bg-dark') }} text-white">
                            <div class="card-body text-center py-3">
                                <div style="font-size:12px; opacity:.85;">Diferencia</div>
                                <div style="font-size:24px; font-weight:700;">
                                    {{ $cierre->diferencia > 0 ? '+' : '' }}{{ $simbolo }} {{ number_format($cierre->diferencia, 2) }}
                                </div>
                                <div style="font-size:12px; opacity:.85;">
                                    @if($cierre->diferencia > 0)
                                        <i class="fas fa-arrow-up"></i> SOBRANTE
                                    @elseif($cierre->diferencia < 0)
                                        <i class="fas fa-arrow-down"></i> FALTANTE
                                    @else
                                        <i class="fas fa-check-circle"></i> CUADRADO
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection