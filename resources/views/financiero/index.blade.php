@extends('layouts.app')

@section('title', 'Estado Financiero')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Estado Financiero</li>
@endsection

@push('styles')
<style>
    .fin-card {
        border-radius: 16px;
        padding: 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
        transition: transform .2s;
    }
    .fin-card:hover { transform: translateY(-3px); }
    .fin-card .fin-icon {
        width: 44px; height: 44px;
        background: rgba(255,255,255,.2);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        margin-bottom: 12px;
    }
    .fin-card .fin-value { font-size: 26px; font-weight: 700; line-height: 1; margin-bottom: 4px; }
    .fin-card .fin-label { font-size: 12px; opacity: .85; margin-bottom: 6px; }
    .fin-card .fin-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(255,255,255,.2); border-radius: 20px;
        padding: 2px 8px; font-size: 11px;
    }
    .bg-gradient-purple { background: linear-gradient(135deg, #a855f7, #7c3aed); }
    .bg-gradient-pink   { background: linear-gradient(135deg, #ec4899, #db2777); }
    .bg-gradient-cyan   { background: linear-gradient(135deg, #06b6d4, #0284c7); }
    .bg-gradient-green  { background: linear-gradient(135deg, #10b981, #059669); }
    .bg-gradient-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
    .bg-gradient-indigo { background: linear-gradient(135deg, #6366f1, #4338ca); }
    .bg-gradient-teal   { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .bg-gradient-rose   { background: linear-gradient(135deg, #f43f5e, #e11d48); }
    .text-value { font-size: 22px; font-weight: 700; color: var(--text-dark); }
    .text-trend-up { color: #10b981; font-size: 13px; font-weight: 500; }
    .text-trend-down { color: #ef4444; font-size: 13px; font-weight: 500; }
    .section-title { font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 16px; }
    .stat-label { font-size: 12px; color: var(--text-muted); }
    .stat-value { font-size: 18px; font-weight: 700; color: var(--text-dark); }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight:600;">
        <i class="fas fa-chart-line me-2" style="color:var(--accent1);"></i>
        Estado Financiero
    </h4>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <label class="form-label mb-0" style="font-size:13px;">Mes:</label>
        <select name="mes" class="form-select form-select-sm" style="width:130px;">
            @foreach($meses as $num => $nom)
                <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nom }}</option>
            @endforeach
        </select>
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
        <div class="fin-card bg-gradient-purple">
            <div class="fin-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="fin-value">S/ {{ number_format($kpis['totalIngresos'], 2) }}</div>
            <div class="fin-label">Ingresos del Mes</div>
            <div class="fin-badge">
                <i class="fas fa-arrow-up"></i>
                Ventas: S/ {{ number_format($kpis['ingresosVentas'], 0) }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fin-card bg-gradient-pink">
            <div class="fin-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="fin-value">S/ {{ number_format($kpis['totalCostos'], 2) }}</div>
            <div class="fin-label">Costos del Mes</div>
            <div class="fin-badge">
                <i class="fas fa-box"></i>
                Costo Ventas: S/ {{ number_format($kpis['costoVentas'], 0) }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fin-card bg-gradient-cyan">
            <div class="fin-icon"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="fin-value">S/ {{ number_format($kpis['gananciaBruta'], 2) }}</div>
            <div class="fin-label">Ganancia Bruta</div>
            <div class="fin-badge">
                <i class="fas fa-percentage"></i>
                Margen: {{ $kpis['margenBruto'] }}%
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="fin-card bg-gradient-green">
            <div class="fin-icon"><i class="fas fa-coins"></i></div>
            <div class="fin-value">S/ {{ number_format($kpis['gananciaNeta'], 2) }}</div>
            <div class="fin-label">Ganancia Neta</div>
            <div class="fin-badge">
                <i class="fas fa-percentage"></i>
                Margen Neto: {{ $kpis['margenNeto'] }}%
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-chart-bar me-2" style="color:var(--accent1);"></i>
                    Ingresos vs Costos (Últimos 12 meses)
                </h6>
                <canvas id="chartIngresosCostos" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-credit-card me-2" style="color:var(--accent1);"></i>
                    Métodos de Pago
                </h6>
                <canvas id="chartMetodosPago" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-pie-chart me-2" style="color:var(--accent1);"></i>
                    Ingresos por Categoría
                </h6>
                <canvas id="chartCategorias" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-tachometer-alt me-2" style="color:var(--accent1);"></i>
                    Indicadores de Liquidez
                </h6>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="stat-label">Efectivo Estimado</div>
                        <div class="stat-value">S/ {{ number_format($kpis['efectivo'], 0) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-label">Cuentas por Cobrar</div>
                        <div class="stat-value">S/ {{ number_format($kpis['cuentasPorCobrar'], 0) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-label">Cuentas por Pagar</div>
                        <div class="stat-value">S/ {{ number_format($kpis['cuentasPorPagar'], 0) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-label">Capital de Trabajo</div>
                        <div class="stat-value {{ $kpis['capitalTrabajo'] >= 0 ? 'text-success' : 'text-danger' }}">
                            S/ {{ number_format($kpis['capitalTrabajo'], 0) }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-label">Valor Inventario (costo)</div>
                        <div class="stat-value">S/ {{ number_format($kpis['valorInventario'], 0) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-label">Rotación Inventario</div>
                        <div class="stat-value">{{ $kpis['rotacionInventario'] }}x</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-table me-2" style="color:var(--accent1);"></i>
                    Serie Mensual de Ingresos, Costos y Ganancia
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Costos</th>
                                <th class="text-end">Ganancia</th>
                                <th class="text-center">Margen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serieMensual as $item)
                                @php $margen = $item['ingresos'] > 0 ? ($item['ganancia'] / $item['ingresos']) * 100 : 0; @endphp
                                <tr>
                                    <td>{{ $item['mes'] }}</td>
                                    <td class="text-end">S/ {{ number_format($item['ingresos'], 2) }}</td>
                                    <td class="text-end">S/ {{ number_format($item['costos'], 2) }}</td>
                                    <td class="text-end {{ $item['ganancia'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        S/ {{ number_format($item['ganancia'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $margen >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($margen, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico 1: Ingresos vs Costos
    const serie = @json($serieMensual);
    const ctx1 = document.getElementById('chartIngresosCostos');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: serie.map(s => s.mes),
                datasets: [
                    {
                        label: 'Ingresos',
                        data: serie.map(s => s.ingresos),
                        backgroundColor: 'rgba(168,85,247,0.6)',
                        borderColor: '#a855f7',
                        borderWidth: 1
                    },
                    {
                        label: 'Costos',
                        data: serie.map(s => s.costos),
                        backgroundColor: 'rgba(236,72,153,0.6)',
                        borderColor: '#ec4899',
                        borderWidth: 1
                    },
                    {
                        label: 'Ganancia',
                        data: serie.map(s => s.ganancia),
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointBackgroundColor: '#10b981',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v.toLocaleString() } }
                }
            }
        });
    }

    // Gráfico 2: Métodos de Pago
    const metodosPago = @json($metodosPago);
    const ctx2 = document.getElementById('chartMetodosPago');
    if (ctx2 && metodosPago.length > 0) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: metodosPago.map(m => m.metodo_pago.charAt(0).toUpperCase() + m.metodo_pago.slice(1)),
                datasets: [{
                    data: metodosPago.map(m => parseFloat(m.monto)),
                    backgroundColor: ['#a855f7', '#ec4899', '#06b6d4', '#10b981', '#f97316'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }

    // Gráfico 3: Ingresos por Categoría
    const categorias = @json($ingresosPorCategoria);
    const ctx3 = document.getElementById('chartCategorias');
    if (ctx3 && categorias.length > 0) {
        const colores = ['#a855f7','#ec4899','#06b6d4','#10b981','#f97316','#6366f1','#14b8a6','#f43f5e'];
        new Chart(ctx3, {
            type: 'pie',
            data: {
                labels: categorias.map(c => c.categoria),
                datasets: [{
                    data: categorias.map(c => parseFloat(c.total)),
                    backgroundColor: colores.slice(0, categorias.length),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } }
                }
            }
        });
    }
});
</script>
@endpush