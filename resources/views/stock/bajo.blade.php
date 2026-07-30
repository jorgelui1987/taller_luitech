@extends('layouts.app')
@section('title', 'Alertas de Stock Bajo')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li>
    <li class="breadcrumb-item active">Alertas de Stock</li>
@endsection

@section('content')
{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card bg-grad-purple">
            <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-value">{{ $totalStockBajo }}</div>
            <div class="kpi-label">Productos con stock bajo</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card bg-grad-pink">
            <div class="kpi-icon"><i class="fas fa-times-circle"></i></div>
            <div class="kpi-value">{{ $totalSinStock }}</div>
            <div class="kpi-label">Productos sin stock</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card bg-grad-cyan">
            <div class="kpi-icon"><i class="fas fa-boxes"></i></div>
            <div class="kpi-value">{{ $productosCriticos }}</div>
            <div class="kpi-label">Críticos (bajo pero con stock)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card bg-grad-green">
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-value">S/ {{ number_format($valorStockBajo, 0) }}</div>
            <div class="kpi-label">Valor en compra del stock bajo</div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search fa-sm"></i></span>
                    <input type="text" class="form-control" name="buscar"
                           placeholder="Nombre o código..." value="{{ request('buscar') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="categoria_id">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ request('categoria_id')==$cat->id?'selected':'' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="marca_id">
                    <option value="">Todas las marcas</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->id }}" {{ request('marca_id')==$m->id?'selected':'' }}>
                            {{ $m->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-1">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <a href="{{ route('stock.bajo') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
            <div class="col-md-2">
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="checkbox" name="sin_stock" id="sinStock"
                           value="1" {{ request('sin_stock')?'checked':'' }}
                           onchange="this.form.submit()">
                    <label class="form-check-label" for="sinStock" style="font-size:13px;">
                        Solo sin stock
                    </label>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Producto</th>
                        <th>Categoría / Marca</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Déficit</th>
                        <th>Precio Venta</th>
                        <th>Valor en Stock</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    @php
                        $deficit = $producto->stock_minimo - $producto->stock;
                        $nivel = $producto->stock <= 0 ? 'critico' : ($producto->stock <= $producto->stock_minimo / 2 ? 'alerta' : 'bajo');
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                @if($producto->imagen)
                                    <img src="{{ asset('storage/'.$producto->imagen) }}"
                                         style="width:40px; height:40px; border-radius:8px; object-fit:cover;">
                                @else
                                    <div style="width:40px; height:40px; border-radius:8px;
                                        background:linear-gradient(135deg,#a855f7,#ec4899);
                                        display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-mobile-alt" style="color:#fff; font-size:14px;"></i>
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:500; font-size:13px;">{{ $producto->nombre }}</div>
                                    <div style="font-size:11px; color:#9ca3af;">{{ $producto->codigo }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:12px;">
                            <div>{{ $producto->categoria->nombre ?? '—' }}</div>
                            <div style="color:#9ca3af;">{{ $producto->marca->nombre ?? '—' }}</div>
                        </td>
                        <td>
                            @if($producto->stock <= 0)
                                <span style="background:#fee2e2; color:#dc2626; border-radius:20px; padding:4px 12px; font-size:13px; font-weight:700;">
                                    {{ $producto->stock }}
                                </span>
                            @elseif($nivel === 'alerta')
                                <span style="background:#fef3c7; color:#92400e; border-radius:20px; padding:4px 12px; font-size:13px; font-weight:700;">
                                    {{ $producto->stock }}
                                </span>
                            @else
                                <span style="background:#fef9c3; color:#854d0e; border-radius:20px; padding:4px 12px; font-size:13px; font-weight:700;">
                                    {{ $producto->stock }}
                                </span>
                            @endif
                        </td>
                        <td style="font-size:13px;">{{ $producto->stock_minimo }}</td>
                        <td>
                            <span style="font-weight:600; color:#dc2626; font-size:13px;">
                                -{{ $deficit > 0 ? $deficit : 0 }}
                            </span>
                        </td>
                        <td style="font-size:13px;">S/ {{ number_format($producto->precio_venta, 2) }}</td>
                        <td style="font-size:13px; color:#6b7280;">
                            S/ {{ number_format($producto->stock * $producto->precio_venta, 2) }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('productos.show', $producto) }}"
                                   class="btn btn-sm" style="background:#f3f4f6; color:#374151; border-radius:8px; padding:5px 10px;" title="Ver">
                                    <i class="fas fa-eye fa-sm"></i>
                                </a>
                                <a href="{{ route('productos.edit', $producto) }}"
                                   class="btn btn-sm" style="background:#ede9fe; color:#7c3aed; border-radius:8px; padding:5px 10px;" title="Editar">
                                    <i class="fas fa-edit fa-sm"></i>
                                </a>
                                <a href="{{ route('stock.bajo.whatsapp', $producto) }}"
                                   class="btn btn-sm" style="background:#e0f2fe; color:#0369a1; border-radius:8px; padding:5px 10px;"
                                   title="Notificar por WhatsApp" target="_blank">
                                    <i class="fab fa-whatsapp fa-sm"></i>
                                </a>
                                <a href="{{ route('stock.ajuste') }}?producto_id={{ $producto->id }}"
                                   class="btn btn-sm" style="background:#d1fae5; color:#065f46; border-radius:8px; padding:5px 10px;" title="Ajustar stock">
                                    <i class="fas fa-exchange-alt fa-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x mb-3 d-block" style="color:#10b981;"></i>
                            <p class="text-muted mb-0" style="font-size:15px; font-weight:500;">¡Todo en orden!</p>
                            <p class="text-muted" style="font-size:13px;">No hay productos con stock bajo en este momento.</p>
                            <a href="{{ route('productos.index') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-box me-1"></i>Ver inventario completo
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($productos->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted" style="font-size:13px;">
                Mostrando {{ $productos->firstItem() }}–{{ $productos->lastItem() }} de {{ $productos->total() }} productos
            </span>
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection