@extends('layouts.app')

@section('title', 'Flujo de Caja')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('financiero.index') }}">Estado Financiero</a></li>
    <li class="breadcrumb-item active" aria-current="page">Flujo de Caja</li>
@endsection

@push('styles')
<style>
    .cf-positive { color: #10b981; }
    .cf-negative { color: #ef4444; }
    .section-title { font-size: 16px; font-weight: 600; color: var(--text-dark); margin-bottom: 16px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0" style="font-weight:600;">
        <i class="fas fa-money-bill-wave me-2" style="color:var(--accent1);"></i>
        Flujo de Caja
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
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size:12px; color:var(--text-muted);">Total Ingresos {{ $year }}</div>
                <div style="font-size:28px; font-weight:700; color:#10b981;">S/ {{ number_format($totalIngresosAnual, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size:12px; color:var(--text-muted);">Total Egresos {{ $year }}</div>
                <div style="font-size:28px; font-weight:700; color:#ef4444;">S/ {{ number_format($totalEgresosAnual, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size:12px; color:var(--text-muted);">Saldo Final {{ $year }}</div>
                <div style="font-size:28px; font-weight:700; {{ $saldoFinal >= 0 ? 'color:#10b981;' : 'color:#ef4444;' }}">
                    S/ {{ number_format($saldoFinal, 2) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-chart-area me-2" style="color:var(--accent1);"></i>
                    Evolución del Flujo de Caja
                </h6>
                <canvas id="chartFlujoCaja" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="fas fa-table me-2" style="color:var(--accent1);"></i>
                    Detalle Mensual
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Egresos</th>
                                <th class="text-end">Saldo del Mes</th>
                                <th class="text-end">Saldo Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mesesData as $item)
                                <tr>
                                    <td><strong>{{ $item['mes'] }}</strong></td>
                                    <td class="text-end cf-positive">S/ {{ number_format($item['ingresos'], 2) }}</td>
                                    <td class="text-end cf-negative">S/ {{ number_format($item['egresos'], 2) }}</td>
                                    <td class="text-end {{ $item['saldo_mes'] >= 0 ? 'cf-positive' : 'cf-negative' }}">
                                        S/ {{ number_format($item['saldo_mes'], 2) }}
                                    </td>
                                    <td class="text-end {{ $item['saldo_acum'] >= 0 ? 'cf-positive' : 'cf-negative' }}" style="font-weight:700;">
                                        S/ {{ number_format($item['saldo_acum'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight:700; border-top:2px solid var(--text-dark);">
                                <td>TOTAL</td>
                                <td class="text-end cf-positive">S/ {{ number_format($totalIngresosAnual, 2) }}</td>
                                <td class="text-end cf-negative">S/ {{ number_format($totalEgresosAnual, 2) }}</td>
                                <td class="text-end {{ $saldoFinal >= 0 ? 'cf-positive' : 'cf-negative' }}">
                                    S/ {{ number_format($totalIngresosAnual - $totalEgresosAnual, 2) }}
                                </td>
                                <td class="text-end {{ $saldoFinal >= 0 ? 'cf-positive' : 'cf-negative' }}">
                                    S/ {{ number_format($saldoFinal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
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
    const data = @json($mesesData);
    const ctx = document.getElementById('chartFlujoCaja');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.mes),
                datasets: [
                    {
                        label: 'Ingresos',
                        data: data.map(d => d.ingresos),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2
                    },
                    {
                        label: 'Egresos',
                        data: data.map(d => d.egresos),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2
                    },
                    {
                        label: 'Saldo Acumulado',
                        data: data.map(d => d.saldo_acum),
                        borderColor: '#a855f7',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.3,
                        pointBackgroundColor: '#a855f7'
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
});
</script>
@endpush