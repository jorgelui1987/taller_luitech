@extends('layouts.app')
@section('title', $producto->nombre)

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li></ul>
    <ul><li class="breadcrumb-item active">{{ $producto->nombre }}</li></ul>
@endsection

@section('content')

@php
    $cond=['nuevo'=>['#d1fae5','#065f46'],'reacondicionado'=>['#e0f2fe','#0369a1'],'usado'=>['#f3f4f6','#374151']];
    $c=$cond[$producto->condicion]??['#f3f4f6','#374151'];
@endphp

{{-- Tarjeta de Resumen --}}
<div class="card mb-4">
    <div class="card-header p-3" style="background:linear-gradient(135deg,#faf5ff,#fdf4ff); border-bottom:1px solid #f3e8ff;">
        <div class="row g-3 align-items-center">
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Producto</div>
                <div style="font-weight:600; font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $producto->nombre }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Marca</div>
                <div style="font-weight:600; font-size:13px;">{{ $producto->marca->nombre ?? '—' }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Stock</div>
                <div style="font-weight:700; font-size:16px; color:{{ $producto->stock <= 0 ? '#dc2626' : ($producto->tieneStockBajo() ? '#d97706' : '#059669') }};">{{ $producto->stock }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Precio venta</div>
                <div style="font-weight:700; font-size:16px; color:#7c3aed;">S/ {{ number_format($producto->precio_venta, 2) }}</div>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Condición</div>
                <span style="background:{{ $c[0] }}; color:{{ $c[1] }}; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; display:inline-block;">
                    {{ ucfirst($producto->condicion) }}
                </span>
            </div>
            <div class="col-md-2 col-6">
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.5px;">Código</div>
                <div style="font-weight:600; font-size:13px;">{{ $producto->codigo }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Panel izquierdo --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body p-4 text-center">
                @if($producto->imagen)
                    <img src="{{ asset('storage/'.$producto->imagen) }}"
                         alt="{{ $producto->nombre }}"
                         style="width:100%; max-height:240px; object-fit:cover; border-radius:14px; margin-bottom:16px;">
                @else
                    <div style="width:100%; height:180px; background:linear-gradient(135deg,#a855f7,#ec4899);
                                border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                        <i class="fas fa-mobile-alt" style="font-size:64px; color:rgba(255,255,255,.6);"></i>
                    </div>
                @endif

                <h5 class="fw-bold mb-1">{{ $producto->nombre }}</h5>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span style="background:#ede9fe; color:#7c3aed; border-radius:20px; padding:3px 10px; font-size:12px;">
                        {{ $producto->marca->nombre ?? '—' }}
                    </span>
                    <span style="background:#f3f4f6; color:#374151; border-radius:20px; padding:3px 10px; font-size:12px;">
                        {{ $producto->categoria->nombre ?? '—' }}
                    </span>
                    @php
                        $cond=['nuevo'=>['#d1fae5','#065f46'],'reacondicionado'=>['#e0f2fe','#0369a1'],'usado'=>['#f3f4f6','#374151']];
                        $c=$cond[$producto->condicion]??['#f3f4f6','#374151'];
                    @endphp
                    <span style="background:{{ $c[0] }}; color:{{ $c[1] }}; border-radius:20px; padding:3px 10px; font-size:12px;">
                        {{ ucfirst($producto->condicion) }}
                    </span>
                </div>

                <hr>

                {{-- Stock indicator --}}
                <div class="mb-3">
                    <div style="font-size:11px; color:#9ca3af; margin-bottom:6px;">STOCK DISPONIBLE</div>
                    @if($producto->stock <= 0)
                        <div style="font-size:28px; font-weight:700; color:#dc2626;">0</div>
                        <div style="font-size:12px; color:#dc2626;">Sin stock</div>
                    @elseif($producto->tieneStockBajo())
                        <div style="font-size:28px; font-weight:700; color:#d97706;">{{ $producto->stock }}</div>
                        <div style="font-size:12px; color:#d97706;">⚠️ Stock bajo (mín. {{ $producto->stock_minimo }})</div>
                    @else
                        <div style="font-size:28px; font-weight:700; color:#059669;">{{ $producto->stock }}</div>
                        <div style="font-size:12px; color:#059669;">Stock óptimo</div>
                    @endif
                    <div class="progress mt-2" style="height:6px; border-radius:4px;">
                        @php $pct = $producto->stock_minimo > 0 ? min(($producto->stock/$producto->stock_minimo)*50, 100) : 100; @endphp
                        <div class="progress-bar" style="width:{{ $pct }}%; background:{{ $producto->stock>$producto->stock_minimo?'#10b981':'#f59e0b' }};"></div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Editar Producto
                    </a>
                    <a href="{{ route('ventas.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Registrar Venta
                    </a>
                </div>
            </div>
        </div>

        {{-- Precios --}}
        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">Precios</h6>
                <div class="d-flex justify-content-between mb-2" style="font-size:13.5px;">
                    <span class="text-muted">Precio Compra</span>
                    <span>S/ {{ number_format($producto->precio_compra, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:13.5px;">
                    <span class="text-muted">Precio Venta</span>
                    <span style="font-weight:700; color:#1e1b4b;">S/ {{ number_format($producto->precio_venta, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between" style="font-size:13.5px;">
                    <span class="text-muted">Margen de ganancia</span>
                    <span style="font-weight:700; color:#10b981;">{{ number_format($producto->margen, 1) }}%</span>
                </div>
                <div class="d-flex justify-content-between mt-1" style="font-size:13px;">
                    <span class="text-muted">Ganancia unitaria</span>
                    <span style="color:#10b981;">S/ {{ number_format($producto->precio_venta - $producto->precio_compra, 2) }}</span>
                </div>
                <div class="mt-3 p-2 rounded-3 text-center" style="background:#f8f5ff; font-size:12px; color:#6b7280;">
                    Valor en stock: <strong style="color:#7c3aed;">S/ {{ number_format($producto->stock * $producto->precio_venta, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel derecho --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header p-0" style="background:#fff; border-bottom:1px solid #e5e7eb;">
                <ul class="nav nav-tabs card-header-tabs" id="productoTabs" role="tablist" style="border-bottom:none; padding:0 8px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-especificaciones-tab" data-bs-toggle="tab" data-bs-target="#tab-especificaciones" type="button" role="tab" aria-controls="tab-especificaciones" aria-selected="true" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                            <i class="fas fa-cogs me-1" style="color:#a855f7;"></i>⚙️ Especificaciones
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-historial-tab" data-bs-toggle="tab" data-bs-target="#tab-historial" type="button" role="tab" aria-controls="tab-historial" aria-selected="false" style="font-size:13px; font-weight:600; color:#6b7280; padding:10px 16px; border:none; border-bottom:2px solid transparent;">
                            <i class="fas fa-shopping-cart me-1" style="color:#a855f7;"></i>🛒 Historial de Ventas
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="productoTabsContent">

                    {{-- Pestaña: Especificaciones --}}
                    <div class="tab-pane fade show active" id="tab-especificaciones" role="tabpanel" aria-labelledby="tab-especificaciones-tab">
                        <div class="row g-3" style="font-size:13.5px;">
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">CÓDIGO SKU</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->codigo }}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">CÓDIGO BARRAS</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->codigo_barras ?: '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">GARANTÍA</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->garantia_dias ? $producto->garantia_dias . ' días' : '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">PROVEEDOR</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->proveedor?->nombre ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">MODELO</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->modelo ?: '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">COLOR</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->color ?: '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">ALMACENAMIENTO</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->almacenamiento ?: '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">RAM</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->ram ?: '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-3 rounded-3 text-center" style="background:#f8f5ff;">
                                    <div style="font-size:10px; color:#9ca3af;">IMEI</div>
                                    <div style="font-weight:600; font-size:13px;">{{ $producto->imei ?: '—' }}</div>
                                </div>
                            </div>
                            @if($producto->descripcion)
                            <div class="col-12">
                                <div class="p-3 rounded-3" style="background:#f8f5ff; font-size:13px; color:#374151;">
                                    <div style="font-size:10px; color:#9ca3af; margin-bottom:4px;">DESCRIPCIÓN</div>
                                    {{ $producto->descripcion }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Pestaña: Historial de Ventas --}}
                    <div class="tab-pane fade" id="tab-historial" role="tabpanel" aria-labelledby="tab-historial-tab">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0">Historial de Ventas</h6>
                            <span style="background:#ede9fe; color:#7c3aed; border-radius:20px; padding:3px 12px; font-size:12px;">
                                {{ $producto->detalleVentas->count() }} ventas
                            </span>
                        </div>

                        @if($producto->detalleVentas->count())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                                <thead>
                                    <tr>
                                        <th>N° Venta</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Cant.</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($producto->detalleVentas->sortByDesc('created_at') as $det)
                                    <tr>
                                        <td>
                                            <a href="{{ route('ventas.show', $det->venta) }}"
                                               style="color:#a855f7; font-weight:500;">
                                                {{ $det->venta->numero_venta ?? '—' }}
                                            </a>
                                        </td>
                                        <td>{{ $det->venta->cliente->nombre_completo ?? '—' }}</td>
                                        <td style="color:#9ca3af;">{{ $det->venta->fecha_venta?->format('d/m/Y') ?? '—' }}</td>
                                        <td>{{ $det->cantidad }}</td>
                                        <td>S/ {{ number_format($det->precio_unitario, 2) }}</td>
                                        <td style="font-weight:600;">S/ {{ number_format($det->subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4 text-muted" style="font-size:13px;">
                            <i class="fas fa-shopping-cart fa-2x mb-2 d-block opacity-40"></i>
                            Este producto aún no ha sido vendido
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
