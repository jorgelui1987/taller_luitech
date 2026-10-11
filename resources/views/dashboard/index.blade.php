@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $marca = auth()->user()->tenant?->colores() ?? [
        'primario'      => '#0891b2',
        'secundario'    => '#3b82f6',
        'primario_rgba' => 'rgba(8,145,178,.18)',
    ];
@endphp

{{-- ── Encabezado ──────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-700" style="font-weight:700;">Panel de Control</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            @if(auth()->user()->esTecnico())
                · <span style="color:#f59e0b;">Técnico</span>
            @elseif(auth()->user()->esVendedor())
                · <span style="color:#06b6d4;">Vendedor</span>
            @else
                · <span style="color:#0891b2;">Administrador</span>
            @endif
        </p>
    </div>
</div>

{{-- ── Accesos Rápidos (todas las pantallas, estilo demo) ─────────────── --}}
<div class="mb-3">
    <div class="row g-2">
        <div class="col-6 col-md-3">
            <a href="{{ route('ventas.create') }}" class="acc-dash">
                <span class="acc-ico" style="background:#10b981;"><i class="fas fa-cash-register"></i></span>
                Nueva Venta
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('reparaciones.create') }}" class="acc-dash">
                <span class="acc-ico" style="background:#f59e0b;"><i class="fas fa-tools"></i></span>
                Nueva Reparación
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('caja.abrir') }}" class="acc-dash">
                <span class="acc-ico" style="background:#06b6d4;"><i class="fas fa-cash-register"></i></span>
                Abrir Caja
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('productos.create') }}" class="acc-dash">
                <span class="acc-ico" style="background:#3b82f6;"><i class="fas fa-box-open"></i></span>
                Nuevo Producto
            </a>
        </div>
        @php $urlPantallaTvDash = auth()->user()->tenant?->urlPantalla(); @endphp
        @if($urlPantallaTvDash)
        <div class="col-6 col-md-3">
            <a href="{{ $urlPantallaTvDash }}" target="_blank" rel="noopener" class="acc-dash">
                <span class="acc-ico" style="background:#0f172a;"><i class="fas fa-tv"></i></span>
                Pantalla en TV
            </a>
        </div>
        @endif
        @php $urlMiPaginaDash = auth()->user()->tenant?->url_publica; @endphp
        @if($urlMiPaginaDash)
        <div class="col-6 col-md-3">
            <a href="{{ $urlMiPaginaDash }}" target="_blank" rel="noopener" class="acc-dash">
                <span class="acc-ico" style="background:linear-gradient(135deg,#0891b2,#3b82f6);"><i class="fas fa-globe"></i></span>
                Ver mi página web
            </a>
        </div>
        <div class="col-6 col-md-3">
            <button type="button" class="acc-dash w-100 border-0" onclick="navigator.clipboard.writeText('{{ $urlMiPaginaDash }}');this.querySelector('.acc-txt').textContent='¡Copiado!';setTimeout(()=>this.querySelector('.acc-txt').textContent='Copiar link',1500);">
                <span class="acc-ico" style="background:#8b5cf6;"><i class="fas fa-link"></i></span>
                <span class="acc-txt">Copiar link</span>
            </button>
        </div>
        @endif
    </div>
</div>
<style>
.acc-dash{background:#fff;border-radius:12px;padding:12px;text-align:center;font-weight:700;font-size:13px;box-shadow:0 2px 8px rgba(0,0,0,.05);text-decoration:none;color:#0f172a;display:block;line-height:1.3;}
a.acc-dash:hover{color:#0f172a;box-shadow:0 4px 14px rgba(0,0,0,.1);}
.acc-ico{width:36px;height:36px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:#fff;margin-bottom:6px;font-size:15px;}
.kpi-new{background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,.06);height:100%;}
.kpi-new .v{font-size:24px;font-weight:800;color:#0f172a;}
.kpi-new .l{font-size:11px;color:#6b7280;letter-spacing:.05em;font-weight:700;}
.met-bar{height:10px;border-radius:99px;background:#eef2f7;overflow:hidden;margin:6px 0 4px;}
.met-bar i{display:block;height:100%;border-radius:99px;}
.dot-m{width:12px;height:12px;border-radius:50%;display:inline-block;margin-right:8px;}
</style>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- 👑 ADMIN: KPI Cards globales ─────────────────────────────────------ --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
@if(auth()->user()->esAdmin() || auth()->user()->esSuperAdmin())
<div class="row g-2 mb-3">
    <div class="col-6 col-xl-3">
        <div class="kpi-new" style="border-left:5px solid #8b5cf6;">
            <div class="l">GANANCIA DE HOY</div>
            <div class="v">{{ formatoMonedaEntero($ventasHoy) }}</div>
            @if(isset($ventasBrutasHoy))
            <div class="mt-2" style="font-size:11px;line-height:1.8;background:#f5f3ff;border-radius:8px;padding:6px 10px;color:#0f172a;">
                <div class="d-flex justify-content-between"><span>Ventas brutas</span><b>{{ formatoMonedaEntero($ventasBrutasHoy) }}</b></div>
                <div class="d-flex justify-content-between"><span>- Devoluciones</span><b>{{ formatoMonedaEntero($devolucionesHoy ?? 0) }}</b></div>
                <div class="d-flex justify-content-between"><span>- Repuestos</span><b>{{ formatoMonedaEntero($costoRepuestosHoy ?? 0) }}</b></div>
            </div>
            @endif
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">
                <i class="fas fa-calendar-day fa-xs"></i>
                {{ formatoMonedaEntero($ventasMes) }} este mes
            </div>
            @if(isset($ventasBrutasMes))
            <div style="font-size:10.5px;color:#6b7280;">
                Brutas {{ formatoMonedaEntero($ventasBrutasMes) }} - Dev {{ formatoMonedaEntero($devolucionesMes ?? 0) }} - Rep {{ formatoMonedaEntero($costoRepuestosMes ?? 0) }}
            </div>
            @endif
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-new" style="border-left:5px solid #ec4899;">
            <div class="l">CLIENTES REGISTRADOS</div>
            <div class="v">{{ number_format($totalClientes) }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">
                <i class="fas fa-user-plus fa-xs"></i>
                +{{ $clientesNuevosMes }} este mes
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-new" style="border-left:5px solid #06b6d4;">
            <div class="l">PRODUCTOS EN STOCK</div>
            <div class="v">{{ number_format($totalProductos) }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">
                @if($stockBajo > 0)
                    <i class="fas fa-exclamation-triangle fa-xs"></i>
                    {{ $stockBajo }} con stock bajo
                @else
                    <i class="fas fa-check fa-xs"></i>
                    Stock óptimo
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-new" style="border-left:5px solid #f59e0b;">
            <div class="l">REPARACIONES ACTIVAS</div>
            <div class="v">{{ $reparacionesPendientes }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">
                <i class="fas fa-check-circle fa-xs"></i>
                {{ $reparacionesListas }} listas para entregar
            </div>
        </div>
    </div>
</div>
@if(isset($porMetodoHoy))
@php
    $coloresMetodo = ['efectivo' => '#10b981', 'mercadopago' => '#3b82f6', 'transferencia' => '#f59e0b', 'tarjeta' => '#8b5cf6', 'debito' => '#06b6d4', 'credito' => '#ec4899', 'cuotas' => '#a855f7', 'yape' => '#7c3aed', 'plin' => '#0ea5e9'];
@endphp
<div class="kpi-new mb-3">
    <div class="l">COBRADO HOY POR MÉTODO (bruto, sin restar) · {{ formatoMonedaEntero($totalBrutoMetodosHoy ?? 0) }} total</div>
    <div class="row g-2 mt-1">
        @forelse($porMetodoHoy as $metodo => $dato)
        @php
            $color = $coloresMetodo[$metodo] ?? '#6b7280';
            $pct = ($totalBrutoMetodosHoy ?? 0) > 0 ? max(3, round($dato->total / $totalBrutoMetodosHoy * 100)) : 3;
        @endphp
        <div class="col-md-4">
            <span class="dot-m" style="background:{{ $color }};"></span><b style="font-size:13px;">{{ ucfirst($metodo) }} {{ formatoMonedaEntero($dato->total) }}</b>
            <span class="text-muted" style="font-size:12px;">({{ $dato->cantidad }} {{ $dato->cantidad == 1 ? 'venta' : 'ventas' }})</span>
            <div class="met-bar"><i style="width:{{ $pct }}%;background:{{ $color }};"></i></div>
        </div>
        @empty
        <div class="col-12 text-muted" style="font-size:13px;">Sin ventas completadas hoy.</div>
        @endforelse
    </div>
    <p class="mb-0 mt-1" style="font-size:11.5px;color:#6b7280;">El efectivo va a tu Caja del día. Lo de Mercado Pago está en tu cuenta MP, no en el cajón. Por eso se separan.</p>
</div>
@endif
@endif

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- 🛒 VENDEDOR: KPI Cards de sus ventas ─────────────────────────------ --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
@if(auth()->user()->esVendedor())
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card bg-grad-purple">
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-value">{{ formatoMonedaEntero($ventasHoy) }}</div>
            <div class="kpi-label">Mis Ventas de Hoy</div>
            <span class="kpi-badge">
                <i class="fas fa-calendar-day fa-xs"></i>
                {{ formatoMonedaEntero($ventasMes) }} este mes
            </span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card bg-grad-pink">
            <div class="kpi-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="kpi-value">{{ $misVentasHoy }}</div>
            <div class="kpi-label">Ventas Realizadas Hoy</div>
            <span class="kpi-badge">
                <i class="fas fa-chart-line fa-xs"></i>
                {{ $misVentasMes }} este mes
            </span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card bg-grad-cyan">
            <div class="kpi-icon"><i class="fas fa-trend-up"></i></div>
            <div class="kpi-value">{{ number_format(abs($crecimientoVentas), 1) }}%</div>
            <div class="kpi-label">Crecimiento Mensual</div>
            <span class="kpi-badge">
                @if($crecimientoVentas >= 0)
                    <i class="fas fa-arrow-up fa-xs"></i> vs mes anterior
                @else
                    <i class="fas fa-arrow-down fa-xs"></i> vs mes anterior
                @endif
            </span>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- 🔧 TÉCNICO: KPI Cards de sus reparaciones y comisiones ─────────---- --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
@if(auth()->user()->esTecnico())
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="kpi-card bg-grad-green">
            <div class="kpi-icon"><i class="fas fa-tools"></i></div>
            <div class="kpi-value">{{ $misReparacionesActivas }}</div>
            <div class="kpi-label">Mis Reparaciones Activas</div>
            <span class="kpi-badge">
                <i class="fas fa-check-circle fa-xs"></i>
                {{ $misReparacionesListas }} listas para entregar
            </span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card bg-grad-cyan">
            <div class="kpi-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="kpi-value">{{ $misEntregadasMes }}</div>
            <div class="kpi-label">Entregadas este Mes</div>
            <span class="kpi-badge">
                <i class="fas fa-calendar-day fa-xs"></i>
                Reparaciones completadas
            </span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card bg-grad-orange">
            <div class="kpi-icon"><i class="fas fa-clock"></i></div>
            <div class="kpi-value">{{ \App\Helpers\FormatoHelper::moneda($misComisionesPendientes) }}</div>
            <div class="kpi-label">Comisiones Pendientes</div>
            <span class="kpi-badge">
                <i class="fas fa-info-circle fa-xs"></i>
                Por cobrar
            </span>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="kpi-card bg-grad-green">
            <div class="kpi-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="kpi-value">{{ \App\Helpers\FormatoHelper::moneda($misComisionesPagadas) }}</div>
            <div class="kpi-label">Comisiones Cobradas</div>
            <span class="kpi-badge">
                <i class="fas fa-check-circle fa-xs"></i>
                Ya pagadas
            </span>
        </div>
    </div>
</div>

{{-- Tarjeta de mi porcentaje de comisión (solo lectura) --}}
<div class="card mb-4" style="border:1px solid #fef3c7; background:linear-gradient(135deg,#fffbeb,#fef3c7);">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;background:#f59e0b;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-percentage" style="color:#fff;font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:13px;color:#92400e;font-weight:500;">Mi porcentaje de comisión</div>
                <div style="font-size:24px;font-weight:700;color:#78350f;">
                    {{ $miComisionPorcentaje !== null ? number_format($miComisionPorcentaje, 1) . '%' : 'No asignado' }}
                </div>
            </div>
        </div>
        <div style="font-size:12px;color:#92400e;max-width:300px;text-align:right;">
            <i class="fas fa-info-circle me-1"></i>
            Se calcula automáticamente al entregar una reparación: Ganancia × % / 100
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Gráficas (ADMIN: globales, VENDEDOR: sus ventas) ─────────────────-- --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
@if(auth()->user()->esAdmin() || auth()->user()->esSuperAdmin() || auth()->user()->esVendedor())
@if($diasSemana->isNotEmpty())
<div class="row g-3 mb-4">
    {{-- Ventas por día --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="mb-0 fw-600" style="font-weight:600;">
                            @if(auth()->user()->esVendedor())Mis Ventas@else Ventas @endif — Últimos 7 días
                        </h6>
                        <span class="text-muted" style="font-size:12px;">Ingresos diarios</span>
                    </div>
                    @if($crecimientoVentas >= 0)
                        <span class="badge" style="background:#d1fae5; color:#065f46; font-size:12px; border-radius:20px; padding:5px 12px;">
                            <i class="fas fa-arrow-up fa-xs"></i> {{ number_format(abs($crecimientoVentas), 1) }}% vs mes anterior
                        </span>
                    @else
                        <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:12px; border-radius:20px; padding:5px 12px;">
                            <i class="fas fa-arrow-down fa-xs"></i> {{ number_format(abs($crecimientoVentas), 1) }}% vs mes anterior
                        </span>
                    @endif
                </div>
                <canvas id="chartVentasDias" height="90"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Productos --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <h6 class="mb-1 fw-600" style="font-weight:600;">Top Productos</h6>
                <p class="text-muted mb-3" style="font-size:12px;">Más vendidos este mes</p>

                @forelse($topProductos as $i => $prod)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff;
                            background: {{ [0 => "linear-gradient(135deg,{$marca['primario_puro']},{$marca['secundario_puro']})", 'linear-gradient(135deg,#3b82f6,#2563eb)', 'linear-gradient(135deg,#06b6d4,#0284c7)', 'linear-gradient(135deg,#10b981,#059669)', 'linear-gradient(135deg,#f59e0b,#d97706)'][$i] }};">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-1" style="min-width:0; flex:1;">
                            <div style="font-size:13px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $prod->nombre }}
                            </div>
                            <div style="font-size:11px; color:#9ca3af;">{{ $prod->total_vendido }} unidades</div>
                        </div>
                        <div style="font-size:13px; font-weight:600; color:#0f172a; white-space:nowrap;">
                            {{ \App\Helpers\FormatoHelper::moneda($prod->ingresos) }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4" style="font-size:13px;">
                        <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                        Sin ventas este mes
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif
@endif

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- Tablas inferiores ──────────────────────────────────────────────── --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}

{{-- ADMIN: Últimas ventas + Reparaciones recientes --}}
@if(auth()->user()->esAdmin() || auth()->user()->esSuperAdmin())
<div class="row g-3">
    {{-- Últimas ventas (PRIMERO - más prominente) --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-600" style="font-weight:600;">Últimas Ventas</h6>
                    <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-primary" style="font-size:12px;">
                        Ver todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>N° Venta</th>
                                <th>Cliente</th>
                                <th>Método</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasVentas as $venta)
                            <tr>
                                <td>
                                    <a href="{{ route('ventas.show', $venta) }}"
                                       style="color:var(--accent1); font-weight:500; font-size:13px; text-decoration:none;">
                                        {{ $venta->numero_venta }}
                                    </a>
                                    <div style="font-size:11px; color:#9ca3af;">
                                        {{ $venta->fecha_venta->diffForHumans() }}
                                    </div>
                                </td>
                                <td style="font-size:13px;">
                                    {{ $venta->cliente->nombre_completo ?? '—' }}
                                </td>
                                <td>
                                    <span style="font-size:12px; text-transform:capitalize;">
                                        {{ $venta->metodo_pago }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:13px; font-weight:600;">
                                        {{ \App\Helpers\FormatoHelper::moneda($venta->total) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $colores = ['completada'=>'success','pendiente'=>'warning','cancelada'=>'danger','devuelta'=>'secondary'];
                                        $color = $colores[$venta->estado] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }}"
                                          style="font-size:11px; border-radius:20px; padding:4px 10px;">
                                        {{ ucfirst($venta->estado) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4" style="font-size:13px;">
                                    <i class="fas fa-shopping-cart fa-2x mb-2 d-block opacity-40"></i>
                                    Sin ventas registradas
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Reparaciones recientes (SEGUNDO) --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-600" style="font-weight:600;">Reparaciones Recientes</h6>
                    <a href="{{ route('reparaciones.index') }}" class="btn btn-sm btn-outline-primary" style="font-size:12px;">
                        Ver todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                @forelse($ultimasReparaciones as $rep)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                    @php
                        $bgEstado = [
                            'recibido'           => '#cffafe',
                            'en_diagnostico'     => '#e0f2fe',
                            'esperando_repuesto' => '#fef9c3',
                            'en_reparacion'      => '#dbeafe',
                            'listo'              => '#d1fae5',
                            'entregado'          => '#f3f4f6',
                            'no_reparable'       => '#fee2e2',
                        ];
                        $icEstado = [
                            'recibido'           => 'fa-inbox',
                            'en_diagnostico'     => 'fa-search',
                            'esperando_repuesto' => 'fa-clock',
                            'en_reparacion'      => 'fa-wrench',
                            'listo'              => 'fa-check',
                            'entregado'          => 'fa-box',
                            'no_reparable'       => 'fa-times',
                        ];
                        $bg = $bgEstado[$rep->estado] ?? '#f3f4f6';
                        $ic = $icEstado[$rep->estado] ?? 'fa-tools';
                    @endphp
                    <div style="width:36px; height:36px; border-radius:10px; background:{{ $bg }};
                                display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas {{ $ic }}" style="font-size:14px; color:#6b7280;"></i>
                    </div>
                    <div class="flex-1" style="min-width:0;">
                        <div style="font-size:13px; font-weight:500;">
                            {{ $rep->dispositivo }}
                            @if($rep->marca) <span style="color:#9ca3af;">— {{ $rep->marca }}</span> @endif
                        </div>
                        <div style="font-size:11px; color:#9ca3af;">
                            {{ $rep->cliente->nombre_completo ?? '—' }} · {{ $rep->numero_orden }}
                        </div>
                    </div>
                    <a href="{{ route('reparaciones.show', $rep) }}"
                       class="btn btn-sm" style="font-size:11px; padding:3px 8px; border-radius:6px;
                       background:#f3f4f6; color:#374151; text-decoration:none;">
                        Ver
                    </a>
                </div>
                @empty
                <div class="text-center text-muted py-4" style="font-size:13px;">
                    <i class="fas fa-tools fa-2x mb-2 d-block opacity-40"></i>
                    Sin reparaciones registradas
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

{{-- VENDEDOR: Solo sus últimas ventas --}}
@if(auth()->user()->esVendedor())
<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-600" style="font-weight:600;">Mis Últimas Ventas</h6>
                    <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-primary" style="font-size:12px;">
                        Ver todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>N° Venta</th>
                                <th>Cliente</th>
                                <th>Método</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasVentas as $venta)
                            <tr>
                                <td>
                                    <a href="{{ route('ventas.show', $venta) }}"
                                       style="color:var(--accent3); font-weight:500; font-size:13px; text-decoration:none;">
                                        {{ $venta->numero_venta }}
                                    </a>
                                </td>
                                <td style="font-size:13px;">
                                    {{ $venta->cliente->nombre_completo ?? '—' }}
                                </td>
                                <td>
                                    <span style="font-size:12px; text-transform:capitalize;">
                                        {{ $venta->metodo_pago }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size:13px; font-weight:600;">
                                        {{ \App\Helpers\FormatoHelper::moneda($venta->total) }}
                                    </span>
                                </td>
                                <td style="font-size:12px; color:#9ca3af;">
                                    {{ $venta->fecha_venta->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    @php
                                        $colores = ['completada'=>'success','pendiente'=>'warning','cancelada'=>'danger','devuelta'=>'secondary'];
                                        $color = $colores[$venta->estado] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }}"
                                          style="font-size:11px; border-radius:20px; padding:4px 10px;">
                                        {{ ucfirst($venta->estado) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4" style="font-size:13px;">
                                    <i class="fas fa-shopping-cart fa-2x mb-2 d-block opacity-40"></i>
                                    No has realizado ventas aún
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- TÉCNICO: Solo sus últimas reparaciones --}}
@if(auth()->user()->esTecnico())
<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-600" style="font-weight:600;">Mis Reparaciones Recientes</h6>
                    <a href="{{ route('reparaciones.index') }}" class="btn btn-sm btn-outline-primary" style="font-size:12px;">
                        Ver todas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>

                @forelse($ultimasReparaciones as $rep)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                    @php
                        $bgEstado = [
                            'recibido'           => '#cffafe',
                            'en_diagnostico'     => '#e0f2fe',
                            'esperando_repuesto' => '#fef9c3',
                            'en_reparacion'      => '#dbeafe',
                            'listo'              => '#d1fae5',
                            'entregado'          => '#f3f4f6',
                            'no_reparable'       => '#fee2e2',
                        ];
                        $icEstado = [
                            'recibido'           => 'fa-inbox',
                            'en_diagnostico'     => 'fa-search',
                            'esperando_repuesto' => 'fa-clock',
                            'en_reparacion'      => 'fa-wrench',
                            'listo'              => 'fa-check',
                            'entregado'          => 'fa-box',
                            'no_reparable'       => 'fa-times',
                        ];
                        $bg = $bgEstado[$rep->estado] ?? '#f3f4f6';
                        $ic = $icEstado[$rep->estado] ?? 'fa-tools';
                    @endphp
                    <div style="width:36px; height:36px; border-radius:10px; background:{{ $bg }};
                                display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fas {{ $ic }}" style="font-size:14px; color:#6b7280;"></i>
                    </div>
                    <div class="flex-1" style="min-width:0;">
                        <div style="font-size:13px; font-weight:500;">
                            {{ $rep->dispositivo }}
                            @if($rep->marca) <span style="color:#9ca3af;">— {{ $rep->marca }}</span> @endif
                        </div>
                        <div style="font-size:11px; color:#9ca3af;">
                            {{ $rep->cliente->nombre_completo ?? '—' }} · {{ $rep->numero_orden }}
                        </div>
                    </div>
                    <div class="text-end" style="flex-shrink:0;">
                        @if(!$rep->comision_pagada && $rep->estado === 'entregado')
                            <span style="background:#fee2e2;color:#991b1b;border-radius:20px;padding:3px 10px;font-size:11px;">
                                Comisión pendiente
                            </span>
                        @elseif($rep->comision_pagada)
                            <span style="background:#d1fae5;color:#065f46;border-radius:20px;padding:3px 10px;font-size:11px;">
                                Comisión pagada
                            </span>
                        @endif
                        <a href="{{ route('reparaciones.show', $rep) }}"
                           class="btn btn-sm mt-1" style="font-size:11px; padding:3px 8px; border-radius:6px;
                           background:#f3f4f6; color:#374151; text-decoration:none;">
                            Ver
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4" style="font-size:13px;">
                    <i class="fas fa-tools fa-2x mb-2 d-block opacity-40"></i>
                    No tienes reparaciones asignadas
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
@if($diasSemana->isNotEmpty())
<script>
// ── Gráfica de ventas por día ───────────────────────────────────────────
const diasLabels  = @json($diasSemana->pluck('fecha'));
const diasTotales = @json($diasSemana->pluck('total'));

const ctxDias = document.getElementById('chartVentasDias').getContext('2d');

const brandStyles = getComputedStyle(document.documentElement);
const brandP = (brandStyles.getPropertyValue('--accent1') || '').trim() || '#0891b2';
const brandS = (brandStyles.getPropertyValue('--accent2') || '').trim() || '#3b82f6';

const gradientFill = ctxDias.createLinearGradient(0, 0, 0, 200);
gradientFill.addColorStop(0, brandP + '4D');
gradientFill.addColorStop(1, brandS + '08');

new Chart(ctxDias, {
    type: 'line',
    data: {
        labels: diasLabels,
        datasets: [{
            label: 'Ventas',
            data: diasTotales,
            borderColor: brandP,
            backgroundColor: gradientFill,
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: brandP,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                titleFont: { family: 'Poppins', size: 12 },
                bodyFont: { family: 'Poppins', size: 13 },
                callbacks: {
                    label: ctx => ' ' + ctx.parsed.y.toLocaleString('es-PE', {minimumFractionDigits: 2})
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { family: 'Poppins', size: 11 }, color: '#9ca3af' }
            },
            y: {
                grid: { color: '#f3f4f6' },
                ticks: {
                    font: { family: 'Poppins', size: 11 },
                    color: '#9ca3af',
                    callback: v => v.toLocaleString('es-PE')
                }
            }
        }
    }
});
</script>
@endif
@endpush
