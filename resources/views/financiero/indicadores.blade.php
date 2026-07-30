@extends('layouts.app')

@section('title', 'Indicadores Financieros')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('financiero.index') }}">Estado Financiero</a></li>
    <li class="breadcrumb-item active" aria-current="page">Indicadores Financieros</li>
@endsection

@push('styles')
<style>
    .section-title { font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 16px; }
    .indicator-card {
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        height: 100%;
        transition: transform .2s;
    }
    .indicator-card:hover { transform: translateY(-2px); }
    .indicator-value { font-size: 32px; font-weight: 800; margin: 8px 0; }
    .indicator-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
    .indicator-desc { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
    .kpi-box {
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        background: #f8f5ff;
        height: 100%;
    }
    .kpi-box .num { font-size: 24px; font-weight: 700; color: var(--accent1); }
    .kpi-box .lbl { font-size: 11px; color: var(--text-muted); text-transform: uppercase; }
    .gauge {
        width: 120px; height: 120px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto;
        font-size: 28px; font-weight: 800; color: #fff;
    }
    .gauge-green { background: linear-gradient(135deg, #10b981, #059669); }
    .gauge-yellow { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .gauge-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .gauge-purple { background: linear-gradient(135deg, #a855f7, #7c3aed); }
    .gauge-blue { background: linear-gradient(135deg, #06b6d4, #0284c7); }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight:600;">
        <i class="fas fa-tachometer-alt me-2" style="color:var(--accent1);"></i>
        Indicadores Financieros
    </h4>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <label class="form-label mb-0" style="font-size:13px;">Año:</label>
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

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-box">
            <div class="lbl">Total Ventas</div>
            <div class="num">S/ {{ number_format($indicadores['totalVentas'], 0) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="background:#fdf2f8;">
            <div class="lbl">Reparaciones</div>
            <div class="num" style="color:var(--accent2);">S/ {{ number_format($indicadores['totalReparaciones'], 0) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="background:#f0fdf4;">
            <div class="lbl">Costo Ventas</div>
            <div class="num" style="color:#10b981;">S/ {{ number_format($indicadores['costoVentas'], 0) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="background:#fef2f2;">
            <div class="lbl">N° Ventas</div>
            <div class="num" style="color:#ef4444;">{{ $indicadores['numVentas'] }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- RENTABILIDAD --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-percentage me-2" style="color:#10b981;"></i>
                    Rentabilidad
                </h6>
                <div class="row g-3">
                    <div class="col-6 text-center">
                        <div class="gauge {{ $indicadores['margenBruto'] >= 30 ? 'gauge-green' : ($indicadores['margenBruto'] >= 15 ? 'gauge-yellow' : 'gauge-red') }}">
                            {{ number_format($indicadores['margenBruto'], 1) }}%
                        </div>
                        <div class="indicator-label mt-2">Margen Bruto</div>
                        <div class="indicator-desc">Ganancia Bruta / Ingresos</div>
                    </div>
                    <div class="col-6">
                        <div class="indicator-card d-flex flex-column justify-content-center h-100" style="background:#f0fdf4;">
                            <div class="indicator-label">Ganancia Bruta</div>
                            <div class="indicator-value" style="color:#10b981;">S/ {{ number_format($indicadores['gananciaBruta'], 0) }}</div>
                            <div class="indicator-desc">Ingresos - Costo de Ventas</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="indicator-card" style="background:#f8f5ff;">
                            <div class="indicator-label">Ticket Promedio</div>
                            <div class="indicator-value" style="color:var(--accent1);">S/ {{ number_format($indicadores['ticketPromedio'], 2) }}</div>
                            <div class="indicator-desc">Total Ventas / N° Ventas</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="indicator-card" style="background:#fefce8;">
                            <div class="indicator-label">Productos Vendidos</div>
                            <div class="indicator-value" style="color:#f59e0b;">{{ number_format($indicadores['numProductosVendidos'], 0) }}</div>
                            <div class="indicator-desc">Unidades vendidas en el año</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LIQUIDEZ --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-water me-2" style="color:#06b6d4;"></i>
                    Liquidez
                </h6>
                <div class="row g-3">
                    <div class="col-6 text-center">
                        <div class="gauge {{ $indicadores['razonCorriente'] >= 2 ? 'gauge-green' : ($indicadores['razonCorriente'] >= 1 ? 'gauge-yellow' : 'gauge-red') }}">
                            {{ $indicadores['razonCorriente'] }}
                        </div>
                        <div class="indicator-label mt-2">Razón Corriente</div>
                        <div class="indicator-desc">Activo C. / Pasivo C.</div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="gauge {{ $indicadores['pruebaAcida'] >= 1 ? 'gauge-blue' : ($indicadores['pruebaAcida'] >= 0.5 ? 'gauge-yellow' : 'gauge-red') }}">
                            {{ $indicadores['pruebaAcida'] }}
                        </div>
                        <div class="indicator-label mt-2">Prueba Ácida</div>
                        <div class="indicator-desc">(AC - Invent.) / Pasivo C.</div>
                    </div>
                    <div class="col-6">
                        <div class="indicator-card" style="background:#f0fdf4;">
                            <div class="indicator-label">Efectivo</div>
                            <div class="indicator-value" style="color:#10b981;">S/ {{ number_format($indicadores['efectivo'], 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="indicator-card" style="background:#fdf2f8;">
                            <div class="indicator-label">Cuentas x Cobrar</div>
                            <div class="indicator-value" style="color:var(--accent2);">S/ {{ number_format($indicadores['cuentasPorCobrar'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- EFICIENCIA --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-running me-2" style="color:#f97316;"></i>
                    Eficiencia Operativa
                </h6>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="indicator-card" style="background:#fff7ed;">
                            <div class="indicator-label">Rotación de Inventario</div>
                            <div class="indicator-value" style="color:#f97316;">{{ $indicadores['rotacionInventario'] }}x</div>
                            <div class="indicator-desc">Costo Ventas / Inventario</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="indicator-card" style="background:#f0fdf4;">
                            <div class="indicator-label">Días de Inventario</div>
                            <div class="indicator-value" style="color:#10b981;">{{ $indicadores['diasInventario'] }} días</div>
                            <div class="indicator-desc">365 / Rotación</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="indicator-card" style="background:#f8f5ff;">
                            <div class="indicator-label">Valor del Inventario</div>
                            <div class="indicator-value" style="color:var(--accent1);">S/ {{ number_format($indicadores['inventario'], 0) }}</div>
                            <div class="indicator-desc">Stock actual valorizado al precio de compra</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ENDEUDAMIENTO --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-hand-holding-usd me-2" style="color:#ef4444;"></i>
                    Endeudamiento
                </h6>
                <div class="row g-3">
                    <div class="col-6 text-center">
                        <div class="gauge {{ $indicadores['endeudamiento'] <= 30 ? 'gauge-green' : ($indicadores['endeudamiento'] <= 60 ? 'gauge-yellow' : 'gauge-red') }}">
                            {{ $indicadores['endeudamiento'] }}%
                        </div>
                        <div class="indicator-label mt-2">Nivel de Endeudamiento</div>
                        <div class="indicator-desc">Pasivos / Activos</div>
                    </div>
                    <div class="col-6">
                        <div class="indicator-card d-flex flex-column justify-content-center h-100" style="background:#fef2f2;">
                            <div class="indicator-label">Cuentas por Pagar</div>
                            <div class="indicator-value" style="color:#ef4444;">S/ {{ number_format($indicadores['cuentasPorPagar'], 0) }}</div>
                            <div class="indicator-desc">Órdenes pendientes</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="indicator-card" style="background:#f0fdf4;">
                            <div class="indicator-label">Total Activos</div>
                            <div class="indicator-value" style="color:#10b981;">S/ {{ number_format($indicadores['totalActivos'], 0) }}</div>
                            <div class="indicator-desc">Efectivo + Cuentas x Cobrar + Inventario</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-chart-line me-2" style="color:var(--accent1);"></i>
                    Evolución Mensual {{ $year }}
                </h6>
                <canvas id="chartEvolucion" height="250"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($evolucionMensual);
    const ctx = document.getElementById('chartEvolucion');
    if (ctx && data.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.mes),
                datasets: [
                    {
                        label: 'Ingresos',
                        data: data.map(d => d.ingresos),
                        backgroundColor: 'rgba(168,85,247,0.6)',
                        borderColor: '#a855f7',
                        borderWidth: 1,
                        order: 2
                    },
                    {
                        label: 'Costos',
                        data: data.map(d => d.costos),
                        backgroundColor: 'rgba(236,72,153,0.6)',
                        borderColor: '#ec4899',
                        borderWidth: 1,
                        order: 2
                    },
                    {
                        label: 'Margen %',
                        data: data.map(d => d.ingresos > 0 ? ((d.ingresos - d.costos) / d.ingresos * 100) : 0),
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: '#10b981',
                        tension: 0.3,
                        yAxisID: 'y1',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        ticks: { callback: v => 'S/ ' + v.toLocaleString() }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { callback: v => v + '%' }
                    }
                }
            }
        });
    }
});
</script>
@endpush