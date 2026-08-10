@extends('layouts.app')
@section('title', 'Códigos de Barras')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li></ul>
    <ul><li class="breadcrumb-item active">🏷️ Códigos de Barras</li></ul>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">🏷️ Generador de Códigos de Barras</h5>
                        <p class="text-muted mb-0" style="font-size:13px;">Genera e imprime etiquetas de código de barras para tus productos</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('productos.codigos-barras') }}" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="buscar" class="form-label">Buscar Producto</label>
                        <input type="text" name="buscar" id="buscar" class="form-control" 
                               placeholder="Buscar por nombre, SKU o código de barras..."
                               value="{{ request('buscar') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="categoria_id" class="form-label">Categoría</label>
                        <select name="categoria_id" id="categoria_id" class="form-select">
                            <option value="">— Todas —</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria_id')==$cat->id?'selected':'' }}>{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filtrar
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered" style="font-size:13px;">
                        <thead>
                            <tr style="background:#f8f5ff; color:#1e1b4b;">
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                                <th style="width:200px;">Código de Barras</th>
                                <th style="width:140px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $p)
                            <tr>
                                <td><span class="badge" style="background:#ede9fe; color:#7c3aed; font-weight:600;">{{ $p->codigo }}</span></td>
                                <td>
                                    <div style="font-weight:600;">{{ $p->nombre }}</div>
                                    <div class="text-muted" style="font-size:11px;">{{ $p->marca->nombre ?? '' }}</div>
                                </td>
                                <td>{{ $p->categoria->nombre ?? '—' }}</td>
                                <td><span class="badge {{ $p->stock <= $p->stock_minimo ? 'bg-danger' : 'bg-success' }}">{{ $p->stock }}</span></td>
                                <td>
                                    @if($p->codigo_barras)
                                        <span class="text-monospace" style="font-family:monospace; font-weight:600; letter-spacing:1px;">{{ $p->codigo_barras }}</span>
                                    @else
                                        <span class="text-muted" style="font-size:12px;">Sin código</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($p->codigo_barras)
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="imprimirEtiqueta({{ $p->id }})" title="Imprimir etiqueta">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="generarCodigo({{ $p->id }}, this)" 
                                                title="{{ $p->codigo_barras ? 'Regenerar' : 'Generar código' }}">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No se encontraron productos.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($productos->count())
                <div class="mt-4">
                    <button type="button" class="btn btn-primary" onclick="imprimirTodas()">
                        <i class="fas fa-print me-1"></i>Imprimir Todas las Etiquetas
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de impresión (oculto) -->
<div id="printArea" style="display:none;"></div>

@endsection

@push('scripts')
<script>
function generarCodigo(productoId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('{{ route("productos.generar-codigo-barras") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ producto_id: productoId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
            alert('Código generado: ' + data.codigo_barras);
            location.reload();
        } else {
            alert(data.message || 'Error al generar el código');
            btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
        }
    })
    .catch(() => {
        alert('Error de conexión');
        btn.innerHTML = '<i class="fas fa-sync-alt"></i>';
    })
    .finally(() => btn.disabled = false);
}

function imprimirEtiqueta(productoId) {
    const productos = @json($productos);
    const p = productos.find(prod => prod.id === productoId);
    if (!p || !p.codigo_barras) return;
    
    const html = `
        <html><head><title>Etiqueta - ${p.nombre}</title>
        <style>
            body { font-family: Arial; margin: 0; padding: 10px; }
            .label { border: 2px solid #000; width: 60mm; height: 30mm; text-align: center; padding: 4px; page-break-after: always; }
            .name { font-size: 9px; font-weight: bold; margin-bottom: 4px; overflow: hidden; }
            .code { font-family: monospace; font-size: 14px; font-weight: bold; letter-spacing: 2px; }
            .sku { font-size: 8px; margin-top: 2px; }
            @media print { body { margin: 0; } .label { border: 2px solid #000; } }
        </style></head><body>
        ${productos.filter(prod => prod.codigo_barras).map(prod => `
            <div class="label">
                <div class="name">${prod.nombre}</div>
                <div class="code">${prod.codigo_barras}</div>
                <div class="sku">SKU: ${prod.codigo}</div>
            </div>
        `).join('')}
        </body></html>
    `;
    
    const win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); }, 500);
}

function imprimirTodas() {
    // Imprime etiquetas para todos los productos con código de barras
    const productos = @json($productos);
    const conCodigo = productos.filter(p => p.codigo_barras);
    
    if (conCodigo.length === 0) {
        alert('No hay productos con código de barras para imprimir.');
        return;
    }
    
    const html = `
        <html><head><title>Etiquetas de Código de Barras</title>
        <style>
            body { font-family: Arial; margin: 0; padding: 10px; }
            .label { border: 2px solid #000; width: 60mm; height: 30mm; text-align: center; padding: 4px; page-break-after: always; display: inline-block; margin: 2px; }
            .name { font-size: 9px; font-weight: bold; margin-bottom: 4px; overflow: hidden; }
            .code { font-family: monospace; font-size: 14px; font-weight: bold; letter-spacing: 2px; }
            .sku { font-size: 8px; margin-top: 2px; }
            @media print { body { margin: 0; } .label { border: 2px solid #000; } }
        </style></head><body>
        ${conCodigo.map(p => `
            <div class="label">
                <div class="name">${p.nombre}</div>
                <div class="code">${p.codigo_barras}</div>
                <div class="sku">SKU: ${p.codigo}</div>
            </div>
        `).join('')}
        </body></html>
    `;
    
    const win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); }, 500);
}
</script>
@endpush