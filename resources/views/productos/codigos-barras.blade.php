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
                        <p class="text-muted mb-0" style="font-size:13px;">
                            Escribe el código de barras (el de fábrica o uno propio) y la cantidad de etiquetas a imprimir
                        </p>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="alert alert-info d-flex align-items-start gap-2" style="font-size:13px;">
                    <i class="fas fa-info-circle mt-1"></i>
                    <div>
                        <strong>¿Cómo funciona?</strong><br>
                        1. Si el producto <strong>trae código de fábrica</strong>, escríbelo tal cual (ej: 7801234567895).<br>
                        2. Si el producto <strong>no trae código</strong>, inventa uno tú mismo (ej: 200000000001).<br>
                        3. Escribe <strong>cuántas etiquetas</strong> quieres imprimir de ese producto.<br>
                        4. Haz clic en <strong>💾 Guardar</strong> y luego en <strong>🖨️ Imprimir</strong>.<br>
                        <span class="text-muted">Cada producto tiene su código ÚNICO e individual. La pistola escáner leerá las barras impresas.</span>
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
                                <th style="width:220px;">Código de Barras</th>
                                <th style="width:100px;">Cantidad</th>
                                <th style="width:180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($productos as $p)
                            <tr id="fila-{{ $p->id }}">
                                <td><span class="badge" style="background:#ede9fe; color:#7c3aed; font-weight:600;">{{ $p->codigo }}</span></td>
                                <td>
                                    <div style="font-weight:600;">{{ $p->nombre }}</div>
                                    <div class="text-muted" style="font-size:11px;">{{ $p->marca->nombre ?? '' }}</div>
                                </td>
                                <td>{{ $p->categoria->nombre ?? '—' }}</td>
                                <td><span class="badge {{ $p->stock <= $p->stock_minimo ? 'bg-danger' : 'bg-success' }}">{{ $p->stock }}</span></td>
                                <td>
                                    @if($p->codigo_barras)
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="text" id="codigo-{{ $p->id }}" class="form-control form-control-sm text-monospace" 
                                                   style="font-family:monospace; font-weight:600; letter-spacing:1px;"
                                                   value="{{ $p->codigo_barras }}">
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="eliminarCodigo({{ $p->id }})" title="Eliminar código">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="text" id="codigo-{{ $p->id }}" class="form-control form-control-sm" 
                                                   placeholder="Escribe el código..." style="font-family:monospace;">
                                            <span class="text-muted" style="font-size:11px; white-space:nowrap;">Sin código</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" id="cantidad-{{ $p->id }}" class="form-control form-control-sm text-center" 
                                           min="1" max="100" value="1">
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="guardarCodigo({{ $p->id }})" title="Guardar código">
                                            <i class="fas fa-save"></i> Guardar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="imprimirEtiqueta({{ $p->id }})" title="Imprimir etiqueta(s)">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No se encontraron productos.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($productos->count())
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="imprimirTodas()">
                        <i class="fas fa-print me-1"></i>Imprimir Todas las Etiquetas
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="guardarTodos()">
                        <i class="fas fa-save me-1"></i>Guardar Todos los Códigos
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
<!-- JsBarcode: genera las barras reales que lee la pistola escáner -->
<script>
// ── Guardar código de barras manual ──────────────────────────────────────
function guardarCodigo(productoId) {
    const input = document.getElementById('codigo-' + productoId);
    const codigo = input.value.trim();
    
    if (!codigo) {
        alert('Escribe el código de barras primero.');
        input.focus();
        return;
    }
    
    // Validar caracteres permitidos
    if (!/^[A-Za-z0-9\-_.]+$/.test(codigo)) {
        alert('El código solo puede contener letras, números, guiones, puntos y guiones bajos.');
        return;
    }
    
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('{{ route("productos.guardar-codigo-barras") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ producto_id: productoId, codigo_barras: codigo })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Código guardado: ' + data.codigo_barras);
            location.reload();
        } else {
            alert(data.message || 'Error al guardar el código');
            btn.innerHTML = originalHtml;
        }
    })
    .catch(() => {
        alert('Error de conexión');
        btn.innerHTML = originalHtml;
    })
    .finally(() => btn.disabled = false);
}

// ── Eliminar código de barras ────────────────────────────────────────────
function eliminarCodigo(productoId) {
    if (!confirm('¿Eliminar el código de barras de este producto? Podrás asignar uno nuevo.')) return;
    
    fetch('{{ route("productos.eliminar-codigo-barras") }}', {
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
            alert('✅ Código eliminado. Ahora puedes escribir uno nuevo.');
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar el código');
        }
    })
    .catch(() => alert('Error de conexión'));
}

// ── Guardar todos los códigos que tengan texto ───────────────────────────
function guardarTodos() {
    const productos = @json($productos);
    let pendientes = productos.filter(p => {
        const input = document.getElementById('codigo-' + p.id);
        return input && input.value.trim() && !p.codigo_barras;
    });
    
    if (pendientes.length === 0) {
        alert('No hay códigos nuevos para guardar. Escribe un código en los productos sin código.');
        return;
    }
    
    if (!confirm('¿Guardar ' + pendientes.length + ' código(s) de barras?')) return;
    
    let guardados = 0;
    let errores = 0;
    let promesas = pendientes.map(p => {
        const codigo = document.getElementById('codigo-' + p.id).value.trim();
        return fetch('{{ route("productos.guardar-codigo-barras") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ producto_id: p.id, codigo_barras: codigo })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) guardados++;
            else { errores++; console.error(data.message); }
        })
        .catch(() => errores++);
    });
    
    Promise.all(promesas).then(() => {
        alert('✅ Guardados: ' + guardados + (errores > 0 ? ' | Errores: ' + errores : ''));
        location.reload();
    });
}

// ── Imprimir etiqueta(s) de un producto ──────────────────────────────────
function imprimirEtiqueta(productoId) {
    const productos = @json($productos);
    const p = productos.find(prod => prod.id === productoId);
    if (!p) return;
    
    // Usar el código del input (por si el usuario lo cambió sin guardar)
    const input = document.getElementById('codigo-' + productoId);
    const codigo = input ? input.value.trim() : p.codigo_barras;
    if (!codigo) {
        alert('Este producto no tiene código de barras. Escribe uno y haz clic en Guardar.');
        return;
    }
    
    const cantidadInput = document.getElementById('cantidad-' + productoId);
    const cantidad = cantidadInput ? parseInt(cantidadInput.value) || 1 : 1;
    
    // Generar las etiquetas con JsBarcode y QR
    const etiquetas = [];
    for (let i = 0; i < cantidad; i++) {
        etiquetas.push(`
            <div class="label">
                <div class="name">${p.nombre}</div>
                <div class="content-row">
                    <svg class="barcode" data-codigo="${codigo}"></svg>
                    <div class="qrcode" data-qr="${codigo}"><canvas></canvas></div>
                </div>
                <div class="code">${codigo}</div>
                <div class="sku">SKU: ${p.codigo}</div>
            </div>
        `);
    }
    
    const html = `
        <html><head><title>Etiqueta - ${p.nombre}</title>
        <style>
            body { font-family: Arial; margin: 0; padding: 10px; }
            .label { border: 2px solid #000; width: 80mm; height: 50mm; text-align: center; padding: 4px; page-break-after: always; display: inline-block; margin: 2px; }
            .name { font-size: 9px; font-weight: bold; margin-bottom: 2px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
            .content-row { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 2px 0; }
            .barcode { width: 80%; height: 60px; }
            .qrcode { width: 65px; height: 65px; }
            .qrcode canvas { width: 100%; height: 100%; }
            .code { font-family: monospace; font-size: 11px; font-weight: bold; letter-spacing: 1px; margin-top: 2px; }
            .sku { font-size: 8px; margin-top: 1px; }
            @media print { body { margin: 0; } .label { border: 2px solid #000; } }
        </style></head><body>
        ${etiquetas.join('')}
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
        <script>
            document.querySelectorAll('.barcode').forEach(svg => {
                try {
                    JsBarcode(svg, svg.dataset.codigo, {
                        format: 'CODE128',
                        width: 4,
                        height: 60,
                        displayValue: false,
                        margin: 2
                    });
                } catch(e) {
                    svg.outerHTML = '<div style="color:red;font-size:10px;">Código inválido</div>';
                }
            });
            document.querySelectorAll('.qrcode').forEach(qr => {
                try {
                    qr.innerHTML = '';
                    new QRCode(qr, {
                        text: qr.dataset.qr,
                        width: 65,
                        height: 65,
                        correctLevel: QRCode.CorrectLevel.L
                    });
                } catch(e) {
                    qr.outerHTML = '<div style="color:red;font-size:10px;">QR inválido</div>';
                }
            });
        <\/script>
        </body></html>
    `;
    
    const win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); }, 800);
}

// ── Imprimir todas las etiquetas ─────────────────────────────────────────
function imprimirTodas() {
    const productos = @json($productos);
    const conCodigo = productos.filter(p => {
        const input = document.getElementById('codigo-' + p.id);
        const codigo = input ? input.value.trim() : p.codigo_barras;
        return codigo;
    });
    
    if (conCodigo.length === 0) {
        alert('No hay productos con código de barras para imprimir.');
        return;
    }
    
    const etiquetas = [];
    conCodigo.forEach(p => {
        const input = document.getElementById('codigo-' + p.id);
        const codigo = input ? input.value.trim() : p.codigo_barras;
        const cantidadInput = document.getElementById('cantidad-' + p.id);
        const cantidad = cantidadInput ? parseInt(cantidadInput.value) || 1 : 1;
        
        for (let i = 0; i < cantidad; i++) {
            etiquetas.push(`
                <div class="label">
                    <div class="name">${p.nombre}</div>
                <div class="content-row">
                    <svg class="barcode" data-codigo="${codigo}"></svg>
                    <div class="qrcode" data-qr="${codigo}"><canvas></canvas></div>
                </div>
                    <div class="code">${codigo}</div>
                    <div class="sku">SKU: ${p.codigo}</div>
                </div>
            `);
        }
    });
    
    const html = `
        <html><head><title>Etiquetas de Código de Barras</title>
        <style>
            body { font-family: Arial; margin: 0; padding: 10px; }
            .label { border: 2px solid #000; width: 80mm; height: 50mm; text-align: center; padding: 4px; page-break-after: always; display: inline-block; margin: 2px; }
            .name { font-size: 9px; font-weight: bold; margin-bottom: 2px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
            .content-row { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 2px 0; }
            .barcode { width: 80%; height: 60px; }
            .qrcode { width: 65px; height: 65px; }
            .qrcode canvas { width: 100%; height: 100%; }
            .code { font-family: monospace; font-size: 11px; font-weight: bold; letter-spacing: 1px; margin-top: 2px; }
            .sku { font-size: 8px; margin-top: 1px; }
            @media print { body { margin: 0; } .label { border: 2px solid #000; } }
        </style></head><body>
        ${etiquetas.join('')}
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
        <script>
            document.querySelectorAll('.barcode').forEach(svg => {
                try {
                    JsBarcode(svg, svg.dataset.codigo, {
                        format: 'CODE128',
                        width: 4,
                        height: 60,
                        displayValue: false,
                        margin: 2
                    });
                } catch(e) {
                    svg.outerHTML = '<div style="color:red;font-size:10px;">Código inválido</div>';
                }
            });
            document.querySelectorAll('.qrcode').forEach(qr => {
                try {
                    qr.innerHTML = '';
                    new QRCode(qr, {
                        text: qr.dataset.qr,
                        width: 65,
                        height: 65,
                        correctLevel: QRCode.CorrectLevel.L
                    });
                } catch(e) {
                    qr.outerHTML = '<div style="color:red;font-size:10px;">QR inválido</div>';
                }
            });
        <\/script>
        </body></html>
    `;
    
    const win = window.open('', '_blank');
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); }, 800);
}
</script>
@endpush