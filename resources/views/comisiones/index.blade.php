@extends('layouts.app')
@section('title', 'Comisiones de Técnicos')

@section('breadcrumb')
    <ul><li class="breadcrumb-item active">Comisiones</li></ul>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Comisiones de Técnicos</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            @if($totalesPorTecnico)<span>Total comisiones del periodo: <strong style="color:#10b981;">{{ \App\Helpers\FormatoHelper::moneda(collect($totalesPorTecnico)->sum('comision_total')) }}</strong></span>@endif
        </p>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Técnico</label>
                <select name="tecnico_id" class="form-select">
                    <option value="">Todos los técnicos</option>
                    @foreach($tecnicos as $t)
                        <option value="{{ $t->id }}" {{ request('tecnico_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filtrar</button>
            </div>
        </form>
    </div>
</div>

{{-- Resumen por técnico --}}
@if(count($totalesPorTecnico) > 0)
<div class="row g-3 mb-4">
    @foreach($totalesPorTecnico as $tid => $total)
    <div class="col-md-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:10px;
                                display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                        {{ strtoupper(substr($total['nombre'], 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:14px;">{{ $total['nombre'] }}</div>
                        <div style="font-size:11px;color:#9ca3af;">{{ $total['cantidad'] }} reparaciones</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                    <span class="text-muted">Base comisión (ganancia)</span>
                    <span>{{ \App\Helpers\FormatoHelper::moneda($total['total_reparado']) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                    <span class="text-muted">Comisión total</span>
                    <span style="font-weight:700;color:#f59e0b;">{{ \App\Helpers\FormatoHelper::moneda($total['comision_total']) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                    <span class="text-muted" style="color:#10b981;">Pagado</span>
                    <span style="color:#10b981;">{{ \App\Helpers\FormatoHelper::moneda($total['comision_pagada']) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:12px;">
                    <span class="text-muted" style="color:#dc2626;">Pendiente</span>
                    <span style="color:#dc2626;font-weight:700;">{{ \App\Helpers\FormatoHelper::moneda($total['comision_pendiente']) }}</span>
                </div>
                @if($total['comision_pendiente'] > 0)
                <form action="{{ route('comisiones.pagar-todo', $total['tecnico']) }}" method="POST"
                      onsubmit="return confirm('¿Marcar TODAS las comisiones pendientes de {{ $total['nombre'] }} como pagadas?')">
                    @csrf
                    <button type="submit" class="btn btn-sm w-100" style="background:#10b981;color:#fff;border-radius:8px;">
                        <i class="fas fa-check-circle me-1"></i>Pagar Todo
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="alert alert-info" style="border-radius:12px;font-size:13px;">
    <i class="fas fa-info-circle me-2"></i>No hay comisiones en este periodo.
</div>
@endif

{{-- Detalle de reparaciones --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th class="ps-4">N° Orden</th>
                        <th>Cliente</th>
                        <th>Técnico</th>
                        <th>Equipo</th>
                        <th>Fecha Entrega</th>
                        <th>Costo</th>
                        <th>%</th>
                        <th>Comisión</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reparaciones as $rep)
                    <tr>
                        <td class="ps-4" style="font-weight:600;color:#a855f7;">{{ $rep->numero_orden }}</td>
                        <td>{{ $rep->cliente->nombre_completo ?? '—' }}</td>
                        <td>{{ $rep->tecnico->name ?? '—' }}</td>
                        <td style="font-size:12px;">{{ $rep->marca }} {{ $rep->modelo }}</td>
                        <td style="font-size:12px;">{{ optional($rep->fecha_entrega)->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $baseComision = $rep->baseComision();
                                $presupuesto = (float)($rep->presupuesto ?? 0);
                                $costoRep = (float)($rep->costo_repuesto ?? 0);
                            @endphp
                            @if($presupuesto > 0)
                                <div style="font-size:12px;">{{ \App\Helpers\FormatoHelper::moneda($presupuesto) }}</div>
                                <div style="font-size:10px; color:#9ca3af;">
                                    Presupuesto {{ \App\Helpers\FormatoHelper::moneda($presupuesto) }} - Repuesto {{ \App\Helpers\FormatoHelper::moneda($costoRep) }}
                                </div>
                                <div style="font-size:10px; color:#f59e0b; font-weight:600;">Base: {{ \App\Helpers\FormatoHelper::moneda($baseComision) }}</div>
                            @else
                                {{ \App\Helpers\FormatoHelper::moneda($rep->costo_final ?: 0) }}
                            @endif
                        </td>
                        <td>{{ $rep->comision_porcentaje }}%</td>
                        <td style="font-weight:700;color:#f59e0b;">{{ \App\Helpers\FormatoHelper::moneda($rep->comision_monto) }}</td>
                        <td>
                            @if($rep->comision_pagada)
                                <span style="background:#d1fae5;color:#065f46;border-radius:20px;padding:3px 10px;font-size:11px;">
                                    <i class="fas fa-check me-1"></i>Pagada
                                </span>
                            @else
                                <span style="background:#fee2e2;color:#991b1b;border-radius:20px;padding:3px 10px;font-size:11px;">
                                    <i class="fas fa-clock me-1"></i>Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if(!$rep->comision_pagada)
                            <form action="{{ route('comisiones.pagar', $rep) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm" style="background:#10b981;color:#fff;border-radius:8px;padding:4px 10px;">
                                    <i class="fas fa-check me-1"></i>Pagar
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-money-bill-wave fa-3x mb-3 d-block" style="color:#d1d5db;"></i>
                            <p class="text-muted mb-0">No hay reparaciones con comisiones en este periodo</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection