@extends('layouts.app')

@section('title', 'Balance General')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('financiero.index') }}">Estado Financiero</a></li>
    <li class="breadcrumb-item active" aria-current="page">Balance General</li>
@endsection

@push('styles')
<style>
    .bs-section { font-weight: 700; font-size: 16px; padding: 10px 0; border-bottom: 2px solid #e9d5ff; margin-top: 16px; }
    .bs-line { padding: 6px 0; display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
    .bs-line-total { font-weight: 700; font-size: 16px; border-top: 2px solid var(--text-dark); padding-top: 8px; margin-top: 4px; }
    .bs-positive { color: #10b981; }
    .bs-negative { color: #ef4444; }
    .bs-indent { padding-left: 24px; }
    .bs-value { font-weight: 600; }
    .bs-divider { border-top: 1px dashed #d1d5db; margin: 4px 0; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight:600;">
        <i class="fas fa-balance-scale me-2" style="color:var(--accent1);"></i>
        Balance General
    </h4>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <label class="form-label mb-0" style="font-size:13px;">Fecha de Corte:</label>
        <input type="date" name="fecha" class="form-control form-control-sm"
               value="{{ $fechaCorte->format('Y-m-d') }}" style="width:160px;">
        <button type="submit" class="btn btn-sm btn-primary px-3">
            <i class="fas fa-filter me-1"></i>Actualizar
        </button>
    </form>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:linear-gradient(135deg, #06b6d4, #0284c7); color:#fff; border-radius:16px 16px 0 0;">
                <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-building me-2"></i>ACTIVOS</h5>
                <small>Al {{ $fechaCorte->format('d/m/Y') }}</small>
            </div>
            <div class="card-body p-4">

                <div class="bs-section" style="color:#0284c7;">ACTIVO CORRIENTE</div>

                <div class="bs-line">
                    <span>Efectivo y Equivalentes</span>
                    <span class="bs-value">S/ {{ number_format($efectivo, 2) }}</span>
                </div>
                <div class="bs-line">
                    <span>Cuentas por Cobrar</span>
                    <span class="bs-value">S/ {{ number_format($cuentasPorCobrar, 2) }}</span>
                </div>
                <div class="bs-line">
                    <span>Inventario (costo)</span>
                    <span class="bs-value">S/ {{ number_format($inventario, 2) }}</span>
                </div>

                <div class="bs-divider"></div>
                <div class="bs-line bs-line-total">
                    <span>Total Activo Corriente</span>
                    <span class="bs-value bs-positive">S/ {{ number_format($totalActivoCorriente, 2) }}</span>
                </div>

                <div class="bs-section mt-3" style="color:#7c3aed;">ACTIVO NO CORRIENTE</div>

                <div class="bs-line">
                    <span>Activo Fijo (estimado)</span>
                    <span class="bs-value">S/ {{ number_format($activoFijo, 2) }}</span>
                </div>

                <div class="bs-divider"></div>
                <div class="bs-line bs-line-total">
                    <span>Total Activo No Corriente</span>
                    <span class="bs-value bs-positive">S/ {{ number_format($activoFijo, 2) }}</span>
                </div>

                <div class="bs-line bs-line-total mt-3" style="font-size:20px;">
                    <span style="color:var(--text-dark);">TOTAL ACTIVOS</span>
                    <span class="bs-positive">S/ {{ number_format($totalActivos, 2) }}</span>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header" style="background:linear-gradient(135deg, #ec4899, #db2777); color:#fff; border-radius:16px 16px 0 0;">
                <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-credit-card me-2"></i>PASIVOS Y PATRIMONIO</h5>
                <small>Al {{ $fechaCorte->format('d/m/Y') }}</small>
            </div>
            <div class="card-body p-4">

                <div class="bs-section" style="color:#db2777;">PASIVO CORRIENTE</div>

                <div class="bs-line">
                    <span>Cuentas por Pagar</span>
                    <span class="bs-value">S/ {{ number_format($cuentasPorPagar, 2) }}</span>
                </div>

                <div class="bs-divider"></div>
                <div class="bs-line bs-line-total">
                    <span>Total Pasivo Corriente</span>
                    <span class="bs-value bs-negative">S/ {{ number_format($pasivosCortoPlazo, 2) }}</span>
                </div>

                <div class="bs-section mt-3" style="color:#7c3aed;">PASIVO NO CORRIENTE</div>

                <div class="bs-line">
                    <span>Pasivos a Largo Plazo</span>
                    <span class="bs-value">S/ {{ number_format($pasivosLargoPlazo, 2) }}</span>
                </div>

                <div class="bs-divider"></div>
                <div class="bs-line bs-line-total">
                    <span>Total Pasivo No Corriente</span>
                    <span class="bs-value bs-negative">S/ {{ number_format($pasivosLargoPlazo, 2) }}</span>
                </div>

                <div class="bs-line bs-line-total mt-2">
                    <span style="color:var(--text-dark);">TOTAL PASIVOS</span>
                    <span class="bs-negative">S/ {{ number_format($totalPasivos, 2) }}</span>
                </div>

                <div class="bs-section mt-3" style="color:#10b981;">PATRIMONIO</div>

                <div class="bs-line">
                    <span>Capital Social</span>
                    <span class="bs-value">S/ {{ number_format($capitalSocial, 2) }}</span>
                </div>
                <div class="bs-line">
                    <span>Utilidades Retenidas</span>
                    <span class="bs-value {{ $utilidadesRetenidas >= 0 ? 'bs-positive' : 'bs-negative' }}">
                        S/ {{ number_format($utilidadesRetenidas, 2) }}
                    </span>
                </div>

                <div class="bs-divider"></div>
                <div class="bs-line bs-line-total">
                    <span>Total Patrimonio</span>
                    <span class="bs-value bs-positive">S/ {{ number_format($totalPatrimonio, 2) }}</span>
                </div>

                <div class="bs-line bs-line-total mt-3" style="font-size:20px;">
                    <span style="color:var(--text-dark);">TOTAL PASIVO + PATRIMONIO</span>
                    <span class="bs-positive">S/ {{ number_format($totalPasivos + $totalPatrimonio, 2) }}</span>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-calculator me-2" style="color:var(--accent1);"></i>
                    Razones Financieras
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background:#f8f5ff; border-radius:12px;">
                            <div style="font-size:12px; color:var(--text-muted);">Razón Corriente</div>
                            <div style="font-size:28px; font-weight:700; color:var(--accent1);">{{ $razonCorriente }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">Activo Corriente / Pasivo Corriente</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background:#fdf2f8; border-radius:12px;">
                            <div style="font-size:12px; color:var(--text-muted);">Prueba Ácida</div>
                            <div style="font-size:28px; font-weight:700; color:var(--accent2);">{{ $pruebaAcida }}</div>
                            <div style="font-size:11px; color:var(--text-muted);">(Act. Corriente - Inventario) / Pasivo Corriente</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3" style="background:#f0fdf4; border-radius:12px;">
                            <div style="font-size:12px; color:var(--text-muted);">Endeudamiento</div>
                            <div style="font-size:28px; font-weight:700; color:#10b981;">{{ $endeudamiento }}%</div>
                            <div style="font-size:11px; color:var(--text-muted);">Pasivo Total / Activo Total</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .section-title { font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 16px; }
</style>
@endpush