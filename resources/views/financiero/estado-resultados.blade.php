@extends('layouts.app')

@section('title', 'Estado de Resultados')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('financiero.index') }}">Estado Financiero</a></li>
    <li class="breadcrumb-item active" aria-current="page">Estado de Resultados</li>
@endsection

@push('styles')
<style>
    .pl-section { font-weight: 700; font-size: 15px; color: var(--text-dark); padding: 8px 0; border-bottom: 2px solid #e9d5ff; }
    .pl-line { padding: 6px 0; display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
    .pl-line-total { font-weight: 700; font-size: 16px; }
    .pl-positive { color: #10b981; }
    .pl-negative { color: #ef4444; }
    .pl-indent { padding-left: 24px; }
    .pl-label { color: var(--text-dark); }
    .pl-value { font-weight: 600; }
    .pl-divider { border-top: 1px dashed #d1d5db; margin: 4px 0; }
    .pl-double-divider { border-top: 3px double var(--text-dark); margin: 8px 0; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight:600;">
        <i class="fas fa-file-invoice-dollar me-2" style="color:var(--accent1);"></i>
        Estado de Resultados
    </h4>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <label class="form-label mb-0" style="font-size:13px;">Periodo:</label>
        <select name="mes" class="form-select form-select-sm" style="width:130px;">
            <option value="">Anual</option>
            @foreach($meses as $num => $nom)
                <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nom }}</option>
            @endforeach
        </select>
        <select name="year" class="form-select form-select-sm" style="width:100px;">
            @foreach($años as $a)
                <option value="{{ $a }}" {{ $year == $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-primary px-3">
            <i class="fas fa-filter me-1"></i>Filtrar
        </button>
    </form>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #a855f7, #7c3aed); color:#fff; border-radius:16px 16px 0 0;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" style="font-weight:600;">
                        <i class="fas fa-chart-pie me-2"></i>Estado de Resultados
                    </h5>
                    <span style="font-size:13px; opacity:.9;">{{ $periodo }}</span>
                </div>
            </div>
            <div class="card-body p-4">

                {{-- INGRESOS --}}
                <div class="pl-section" style="color:#7c3aed;">INGRESOS</div>

                <div class="pl-line">
                    <span class="pl-label">Ventas de Productos ({{ $ventas->cantidad }} transacciones)</span>
                    <span class="pl-value">S/ {{ number_format($ventas->total ?? 0, 2) }}</span>
                </div>
                @if($ventas->descuento > 0)
                <div class="pl-line pl-indent">
                    <span class="pl-label">Descuentos otorgados</span>
                    <span class="pl-value pl-negative">- S/ {{ number_format($ventas->descuento, 2) }}</span>
                </div>
                @endif
                @if($ventas->impuesto > 0)
                <div class="pl-line pl-indent">
                    <span class="pl-label">IGV / Impuesto</span>
                    <span class="pl-value">S/ {{ number_format($ventas->impuesto, 2) }}</span>
                </div>
                @endif

                <div class="pl-line">
                    <span class="pl-label">Reparaciones ({{ $reparaciones->cantidad }} servicios)</span>
                    <span class="pl-value">S/ {{ number_format($reparaciones->total ?? 0, 2) }}</span>
                </div>

                <div class="pl-divider"></div>
                <div class="pl-line pl-line-total">
                    <span class="pl-label">Total Ingresos</span>
                    <span class="pl-value pl-positive">S/ {{ number_format($totalIngresos, 2) }}</span>
                </div>

                {{-- COSTOS --}}
                <div class="pl-section mt-3" style="color:#db2777;">COSTOS</div>

                <div class="pl-line">
                    <span class="pl-label">Costo de Ventas (productos)</span>
                    <span class="pl-value">S/ {{ number_format($costoVentas, 2) }}</span>
                </div>
                <div class="pl-line">
                    <span class="pl-label">Costo de Reparaciones</span>
                    <span class="pl-value">S/ {{ number_format($costoReparaciones, 2) }}</span>
                </div>

                <div class="pl-divider"></div>
                <div class="pl-line pl-line-total">
                    <span class="pl-label">Total Costos</span>
                    <span class="pl-value pl-negative">S/ {{ number_format($totalCostos, 2) }}</span>
                </div>

                {{-- UTILIDAD BRUTA --}}
                <div class="pl-double-divider"></div>
                <div class="pl-line pl-line-total">
                    <span class="pl-label" style="font-size:18px;">Utilidad Bruta</span>
                    <span class="pl-value {{ $utilidadBruta >= 0 ? 'pl-positive' : 'pl-negative' }}" style="font-size:20px;">
                        S/ {{ number_format($utilidadBruta, 2) }}
                    </span>
                </div>
                <div class="pl-line pl-indent">
                    <span class="pl-label" style="font-size:12px; color:var(--text-muted);">Margen Bruto</span>
                    <span class="pl-value" style="font-size:14px;">
                        <span class="badge {{ $margenBruto >= 0 ? 'bg-success' : 'bg-danger' }}">{{ number_format($margenBruto, 1) }}%</span>
                    </span>
                </div>

                {{-- GASTOS OPERATIVOS --}}
                <div class="pl-section mt-3" style="color:#f97316;">GASTOS OPERATIVOS</div>

                <div class="pl-line">
                    <span class="pl-label">Gastos Administrativos (Compras)</span>
                    <span class="pl-value">S/ {{ number_format($gastosAdmin, 2) }}</span>
                </div>

                <div class="pl-divider"></div>
                <div class="pl-line pl-line-total">
                    <span class="pl-label">Total Gastos Operativos</span>
                    <span class="pl-value pl-negative">S/ {{ number_format($totalGastosOperativos, 2) }}</span>
                </div>

                {{-- UTILIDAD OPERATIVA --}}
                <div class="pl-double-divider"></div>
                <div class="pl-line pl-line-total">
                    <span class="pl-label" style="font-size:16px;">Utilidad Operativa (EBIT)</span>
                    <span class="pl-value {{ $utilidadOperativa >= 0 ? 'pl-positive' : 'pl-negative' }}" style="font-size:18px;">
                        S/ {{ number_format($utilidadOperativa, 2) }}
                    </span>
                </div>
                <div class="pl-line pl-indent">
                    <span class="pl-label" style="font-size:12px; color:var(--text-muted);">Margen Operativo</span>
                    <span class="pl-value">
                        <span class="badge {{ $margenOperativo >= 0 ? 'bg-success' : 'bg-danger' }}">{{ number_format($margenOperativo, 1) }}%</span>
                    </span>
                </div>

                {{-- GASTOS FINANCIEROS --}}
                @if($gastosFinancieros > 0)
                <div class="pl-line mt-2">
                    <span class="pl-label">Gastos Financieros</span>
                    <span class="pl-value pl-negative">S/ {{ number_format($gastosFinancieros, 2) }}</span>
                </div>
                @endif

                {{-- UTILIDAD NETA --}}
                <div class="pl-double-divider"></div>
                <div class="pl-line pl-line-total">
                    <span class="pl-label" style="font-size:20px; color:var(--accent1);">Utilidad Neta</span>
                    <span class="pl-value {{ $utilidadNeta >= 0 ? 'pl-positive' : 'pl-negative' }}" style="font-size:22px;">
                        S/ {{ number_format($utilidadNeta, 2) }}
                    </span>
                </div>
                <div class="pl-line pl-indent">
                    <span class="pl-label" style="font-size:12px; color:var(--text-muted);">Margen Neto</span>
                    <span class="pl-value">
                        <span class="badge {{ $margenNeto >= 0 ? 'bg-success' : 'bg-danger' }}" style="font-size:13px;">
                            {{ number_format($margenNeto, 1) }}%
                        </span>
                    </span>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection