@extends('layouts.app')
@section('title', 'Nueva Venta')
@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li></ul>
    <ul><li class="breadcrumb-item active">Nueva Venta</li></ul>
@endsection
@section('content')
<div class="container-fluid px-1">
    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">Registrar Venta</h5>
        </div>
        <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary btn-sm">Ver Ventas</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $e)
                <div style="font-size:13px;"><i class="fas fa-exclamation-circle me-1"></i>{{ $e }}</div>
            @endforeach
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="font-size:13px;">{{ session('error') }}</div>
    @endif
    <form action="{{ route('ventas.store') }}" method="POST">
        @csrf
        <div class="row g-2 align-items-start">
            <div class="col-xl-7 col-lg-8">
                <x-st-block color="#0284c7" titulo="1. Cliente + Productos">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                    <x-st-select label="Cliente (opcional)" name="cliente_id">
                        <option value="">— Sin cliente (venta general) —</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre_completo }} — {{ $c->telefono }}</option>
                        @endforeach
                    </x-st-select>
                        </div>
                        <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search fa-sm"></i></span>
                                    <label for="buscador" class="visually-hidden">Buscar producto</label>
                                    <input type="text" id="buscador" class="form-control form-control-sm" placeholder="Buscar producto (nombre, SKU o código)..." autocomplete="off" oninput="filtrarAjax()">
                                </div>
                                <div id="resultBuscador" class="list-group mt-1" style="display:none; max-height:220px; overflow-y:auto; position:absolute; z-index:50; width:100%;"></div>
                        </div>
                        <div class="col-md-3">
                                <label for="selectProd" class="visually-hidden">Seleccionar producto</label>
                                <select id="selectProd" class="form-select form-select-sm">
                                    <option value="">— Seleccionar —</option>
                                        @foreach($productos as $p)
                                            <option value="{{ $p->id }}" data-precio="{{ $p->precio_venta }}" data-stock="{{ $p->stock }}" data-codigo="{{ $p->codigo }}" data-codigo_barras="{{ $p->codigo_barras ?? '' }}">{{ $p->nombre }} ({{ $p->stock }})</option>
                                        @endforeach
                                </select>
                        </div>
                    </div>
                    <div class="row g-2 align-items-end mt-1">
                            <div class="col-md-6">
                                <label for="codBarras" class="visually-hidden">Código de barras</label>
                                <input type="text" id="codBarras" class="form-control form-control-sm" placeholder="📷 Escanea y se agrega solo..." style="font-family:monospace;" autocomplete="off" autofocus onkeydown="if(event.key==='Enter'){event.preventDefault();buscarCodigo(this.value);}" oninput="inputPistola(this.value)">
                                <small class="text-muted" style="font-size:11px;">Escanea y se agrega solo, sin Enter ni clic.</small>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn ui-btn-brand btn-sm w-100" onclick="agregar()"><i class="fas fa-plus me-1"></i>Agregar</button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="iniciarScanner()"><i class="fas fa-camera me-1"></i>Escanear</button>
                            </div>
                        </div>
                        <input type="file" id="scannerInput" accept="image/*" capture="environment" style="display:none;" onchange="procesarFotoCodigo(this)">
                        <div id="scannerContainer" style="display:none; max-width:400px; margin-top:8px;" class="card p-2">
                            <div id="scannerMensaje" style="font-size:13px; padding:10px; text-align:center; color:#6b7280;">
                                <i class="fas fa-camera fa-2x mb-2" style="color:#0891b2;"></i><br>
                                Preparando escáner...
                            </div>
                            <div id="scannerVideoWrap" style="position:relative; display:none;">
                                <video id="scannerVideo" playsinline muted style="width:100%; border-radius:8px; background:#000;"></video>
                                <div id="scannerOverlay" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;">
                                    <div id="scannerBox" style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:80%; height:60px; border:3px solid #0891b2; border-radius:8px; box-shadow:0 0 0 9999px rgba(0,0,0,0.4);"></div>
                                    <div id="scannerLine" style="position:absolute; left:10%; width:80%; height:2px; background:#22c55e; box-shadow:0 0 8px #22c55e; animation:scanline 2s linear infinite;"></div>
                                </div>
                            </div>
                            <style>
                                @@keyframes scanline {
                                    0% { top: 20%; }
                                    50% { top: 80%; }
                                    100% { top: 20%; }
                                }
                            </style>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="detenerScanner()"><i class="fas fa-times me-1"></i>Cerrar</button>
                        </div>
                    <div class="table-responsive mt-2 mb-0" style="max-height:220px;overflow-y:auto;border:1px solid #eef2f7;border-radius:10px;">
                        <table class="table table-sm align-middle mb-0" style="font-size:13px;">
                            <thead class="table-light" style="position:sticky;top:0;"><tr><th>Producto</th><th style="width:60px;">Cant.</th><th style="width:90px;">Precio</th><th style="width:70px;">Dto.</th><th style="width:90px;">Subtotal</th><th style="width:32px;"></th></tr></thead>
                            <tbody id="cuerpo">
                                <tr id="vacio"><td colspan="6" class="text-center text-muted py-3">Agrega productos a la venta</td></tr>
                            </tbody>
                        </table>
                    </div>
                </x-st-block>
                <x-st-block color="#f59e0b" titulo="2. Cupón y notas">
                    <div class="row g-2">
                        <div class="col-md-7">
                            <label for="cuponInput" class="st-label">Cupón</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="cupon_codigo" id="cuponInput"
                                       placeholder="CUP-XXXXXX"
                                       style="text-transform:uppercase;" autocomplete="off">
                                <button type="button" class="btn btn-outline-success" id="btnValidarCupon" onclick="validarCupon()">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                            <div id="cuponMsg" class="form-text mt-1" style="font-size:12px;"></div>
                        </div>
                        <div class="col-md-5">
                            <label for="notas" class="st-label">Notas</label>
                            <textarea class="st-input" name="notas" id="notas" rows="1" placeholder="Opcional"></textarea>
                        </div>
                    </div>
                </x-st-block>
            </div>
            <div class="col-xl-5 col-lg-4">
                <div style="position:sticky;top:12px;">
                <x-st-block color="#10b981" titulo="3. Pago y totales">
                    <div class="mb-2 p-2 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="d-flex flex-wrap gap-1" id="payPills">
                            @if(($empresa->mercadopago_activo ?? false))
                            <button type="button" class="btn btn-sm btn-outline-primary pay-pill" data-val="mercadopago"><i class="fas fa-wallet me-1"></i>MP</button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-success pay-pill active" data-val="efectivo"><i class="fas fa-money-bill-wave me-1"></i>Efectivo</button>
                            <button type="button" class="btn btn-sm btn-outline-warning pay-pill" data-val="tarjeta"><i class="fas fa-credit-card me-1"></i>Tarjeta</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary pay-pill" data-val="transferencia"><i class="fas fa-exchange-alt me-1"></i>Transf.</button>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 d-none">
                            <x-st-select label="Método de Pago" name="metodo_pago" value="efectivo" required="1">
                                @if(($empresa->mercadopago_activo ?? false))
                                <option value="mercadopago">Mercado Pago</option>
                                @endif
                                <option value="efectivo" selected>Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </x-st-select>
                        </div>
                        <div class="col-12">
                            <x-st-input label="Descuento" name="descuento_general" id="descGen" type="number" value="0" extra='min="0" step="0.01" oninput="totales()"'></x-st-input>
                        </div>
                    </div>
                    <div class="mt-2 p-2 rounded-3" style="background:#f0fdf4;border:1px dashed #86efac;">
                        <div class="d-flex justify-content-between mb-1" style="font-size:13px;"><span class="text-muted">Valor</span><span id="lblSubtotal">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    @if(($empresa->pais ?? '') === 'CL')
                    <div class="d-flex justify-content-between mb-1" style="font-size:13px;"><span class="text-muted">Neto</span><span id="lblNeto">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    @endif
                    <div class="d-flex justify-content-between mb-1" style="font-size:13px;"><span class="text-muted">Descuento</span><span id="lblDescuento" class="text-danger">— {{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    <div class="d-flex justify-content-between mb-1" id="cuponRow" style="display:none;font-size:13px;">
                        <span>Cupón <span id="lblCuponCodigo" class="text-success"></span></span>
                        <span id="lblCuponDescuento" class="text-danger">— {{ $empresa->simbolo_moneda ?? '$' }} 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:13px;"><span class="text-muted">{{ $empresa->pais == 'CL' ? 'IVA' : 'IGV' }} ({{ $empresa->igv ?? 18 }}%)</span><span id="lblIgv">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mb-1"><strong>Total</strong><span id="lblTotal" style="font-size:22px;font-weight:800;color:var(--accent1);">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                        <div id="lblTotalMini" style="display:none;">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</div>
                    </div>
                    <div class="mt-2" id="rowEfectivo" style="display:none;">
                        <label for="montoRecibido" class="st-label">Recibido</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">{{ $empresa->simbolo_moneda ?? '$' }}</span>
                            <input type="number" class="form-control" name="monto_recibido" id="montoRecibido"
                                   min="0" step="1" placeholder="¿Con cuánto paga?" oninput="actualizarVuelto()">
                        </div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRecibido(10000)">10k</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRecibido(20000)">20k</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRecibido(50000)">50k</button>
                        </div>
                        <div id="lblVuelto" class="mt-2" style="display:none;font-size:18px;font-weight:700;color:#059669;"></div>
                        <div id="lblFalta" class="mt-1 text-danger" style="display:none;font-size:12px;"></div>
                    </div>
                    <div class="mt-2" id="rowReferencia" style="display:none;">
                        <label for="referenciaPago" class="st-label">N° operación / comprobante <span class="text-muted">(opcional)</span></label>
                        <input type="text" class="form-control form-control-sm" name="referencia_pago" id="referenciaPago"
                               maxlength="100" placeholder="Ej: voucher, N° transferencia..." autocomplete="off"
                               style="font-family:monospace;text-transform:uppercase;">
                        <small class="text-muted" style="font-size:11px;" id="referenciaHint">N° de transferencia o comprobante.</small>
                    </div>
                    <input type="hidden" name="cobrar_con_point" id="cobrarConPoint" value="0">
                    <button type="submit" class="btn w-100 py-3 mt-2" id="btnReg" disabled
                        style="background:#22d3ee;border:none;border-radius:12px;color:#0f172a;font-weight:800;font-size:16px;letter-spacing:1px;"
                        onclick="document.getElementById('cobrarConPoint').value='0';">
                        <i class="fas fa-cash-register me-2"></i>COBRAR
                    </button>
                </x-st-block>
                </div>
            </div>
        </div>
                </form>
</div>
@endsection
@push('scripts')
<script>
let items = {};
let idx = 0;
let selProd = document.getElementById('selectProd');
let cuerpo = document.getElementById('cuerpo');
let vacio = document.getElementById('vacio');
let btnReg = document.getElementById('btnReg');
let scannerActivo = false;

// La pistola escribe rapidisimo y termina con Enter.
// Si el foco estaba fuera de un input, igual capturamos y agregamos directo.
let bufferPistola = '';
let timerBufferPistola = null;
document.addEventListener('keydown', function(e) {
    let tag = (document.activeElement && document.activeElement.tagName) || '';
    let enCampo = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
    if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey && !enCampo) {
        bufferPistola += e.key;
        clearTimeout(timerBufferPistola);
        timerBufferPistola = setTimeout(function() { bufferPistola = ''; }, 400);
    } else if (e.key === 'Enter' && bufferPistola.length >= 3 && !enCampo) {
        e.preventDefault();
        let cod = bufferPistola;
        bufferPistola = '';
        clearTimeout(timerBufferPistola);
        buscarCodigo(cod);
    }
});
document.addEventListener('DOMContentLoaded', function() {
    let input = document.getElementById('codBarras');
    if (input) input.focus();
});

function filtrar() {
    let q = document.getElementById('buscador').value.toLowerCase();
    for (let i = 1; i < selProd.options.length; i++) {
        selProd.options[i].hidden = !selProd.options[i].text.toLowerCase().includes(q);
    }
}

// ── Buscador AJAX sin límite: trae de TODA la BD (miles de accesorios) ──
// Se dispara al escribir 2+ letras; filtra el select precargado y además
// consulta /api/productos/buscar para productos fuera de los primeros 50.
let buscadorTimer = null;
function filtrarAjax() {
    filtrar(); // filtro instantáneo sobre lo precargado
    let q = document.getElementById('buscador').value.trim();
    let box = document.getElementById('resultBuscador');
    clearTimeout(buscadorTimer);
    if (q.length < 2) { box.style.display = 'none'; box.innerHTML = ''; return; }
    buscadorTimer = setTimeout(() => {
        fetch("{{ route('api.productos.buscar') }}?q=" + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(data => {
            // Quitar los que ya están en el select para no duplicar
            let existentes = new Set();
            for (let i = 1; i < selProd.options.length; i++) existentes.add(String(selProd.options[i].value));
            let nuevos = (data || []).filter(p => !existentes.has(String(p.id)));
            if (!nuevos.length) { box.style.display = 'none'; box.innerHTML = ''; return; }
            box.innerHTML = nuevos.map(p =>
                `<button type="button" class="list-group-item list-group-item-action py-1" style="font-size:13px;"
                    data-id="${p.id}" data-precio="${p.precio_venta}" data-stock="${p.stock}" data-codigo="${p.codigo ?? ''}" data-codigo_barras="${p.codigo_barras ?? ''}" data-nombre="${(p.nombre || '').replace(/"/g, '&quot;')}">
                    <strong>${p.nombre}</strong> <small class="text-muted">(${(p.codigo || '')}) — Stock: ${p.stock}</small>
                </button>`).join('');
            box.style.display = 'block';
            box.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', () => {
                    agregarDesdeDatos(btn.dataset);
                    box.style.display = 'none';
                    document.getElementById('buscador').value = '';
                });
            });
        }).catch(() => { box.style.display = 'none'; });
    }, 250);
}

// Cierra sugerencias al hacer clic fuera
document.addEventListener('click', function(e) {
    let box = document.getElementById('resultBuscador');
    if (box && !box.contains(e.target) && e.target.id !== 'buscador') box.style.display = 'none';
});

function agregarDesdeDatos(d) {
    let id = d.id, precio = parseFloat(d.precio), stock = parseInt(d.stock), nombre = d.nombre;
    if (items[id]) {
        let inp = document.querySelector('#fila-' + id + ' .cant');
        let c = parseInt(inp.value) + 1;
        if (c > stock) { alert('Stock insuficiente'); return; }
        inp.value = c; recalc(id);
    } else {
        items[id] = { nombre, precio, stock };
        vacio.style.display = 'none';
        let tr = document.createElement('tr'); tr.id = 'fila-' + id;
        tr.innerHTML = `<td><input type="hidden" name="productos[${idx}][id]" value="${id}"><strong>${nombre}</strong><br><small style="color:#999;">Stock: ${stock}</small></td>
            <td><input type="number" name="productos[${idx}][cantidad]" value="1" min="1" max="${stock}" class="form-control form-control-sm cant" style="width:65px;" onchange="recalc('${id}')"></td>
            <td>{{ $empresa->simbolo_moneda ?? '$' }} ${precio.toFixed(2)}</td>
            <td><input type="number" name="productos[${idx}][descuento]" value="0" min="0" step="0.01" class="form-control form-control-sm" style="width:80px;" onchange="recalc('${id}')"></td>
            <td id="sub-${id}"><strong>{{ $empresa->simbolo_moneda ?? '$' }} ${precio.toFixed(2)}</strong></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="quitar('${id}')"><i class="fas fa-times"></i></button></td>`;
        cuerpo.appendChild(tr); idx++;
    }
    totales();
}

function normalizarCodigo(s) {
    return (s || '').toString().trim();
}

let timerPistola = null;
function inputPistola(v) {
    clearTimeout(timerPistola);
    v = normalizarCodigo(v);
    if (!v) return;
    // La pistola escribe muy rapido: si en 300ms no llegan mas caracteres, agregar solo
    timerPistola = setTimeout(function() {
        let input = document.getElementById('codBarras');
        if (normalizarCodigo(input.value) !== '') buscarCodigo(input.value);
    }, 300);
}

function buscarCodigo(cod) {
    cod = normalizarCodigo(cod);
    if (!cod) return false;
    let q = cod.toLowerCase();
    // 1) Coincidencia exacta en el select precargado (codigo de barras o SKU)
    for (let i = 1; i < selProd.options.length; i++) {
        let opt = selProd.options[i];
        let cb = normalizarCodigo(opt.dataset.codigo_barras || '').toLowerCase();
        let sku = normalizarCodigo(opt.dataset.codigo || '').toLowerCase();
        if ((cb !== '' && cb === q) || (sku !== '' && sku === q)) {
            agregarDesdeDatos({ id: opt.value, precio: opt.dataset.precio, stock: opt.dataset.stock, nombre: opt.text.split(' (')[0] });
            document.getElementById('codBarras').value = '';
            document.getElementById('codBarras').focus();
            return true;
        }
    }
    // 2) No esta precargado (ej: fuera de los primeros 50): buscar en servidor y agregar directo
    fetch("{{ route('api.productos.buscar') }}?q=" + encodeURIComponent(cod), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(data => {
        let lista = Array.isArray(data) ? data : (data.data || []);
        let exacto = lista.find(p => normalizarCodigo(p.codigo_barras || '').toLowerCase() === q || normalizarCodigo(p.codigo || '').toLowerCase() === q);
        let elegido = exacto || (lista.length === 1 ? lista[0] : null);
        if (elegido) {
            agregarDesdeDatos({ id: elegido.id, precio: elegido.precio_venta, stock: elegido.stock, nombre: elegido.nombre });
            document.getElementById('codBarras').value = '';
            document.getElementById('codBarras').focus();
        } else if (lista.length > 1) {
            document.getElementById('buscador').value = cod;
            filtrarAjax();
            document.getElementById('buscador').focus();
        } else {
            alert('Producto no encontrado con código: ' + cod);
            let input = document.getElementById('codBarras');
            input.focus(); input.select();
        }
    }).catch(() => {
        alert('Producto no encontrado con código: ' + cod);
        let input = document.getElementById('codBarras');
        input.focus(); input.select();
    });
    return false;
}

function agregar() {
    let opt = selProd.options[selProd.selectedIndex];
    if (!opt.value) { alert('Selecciona un producto'); return; }
    let id = opt.value;
    let precio = parseFloat(opt.dataset.precio);
    let stock = parseInt(opt.dataset.stock);
    let nombre = opt.text.split(' (')[0];
    if (items[id]) {
        let inp = document.querySelector('#fila-' + id + ' .cant');
        let c = parseInt(inp.value) + 1;
        if (c > stock) { alert('Stock insuficiente'); return; }
        inp.value = c; recalc(id);
    } else {
        items[id] = { nombre, precio, stock };
        vacio.style.display = 'none';
        let tr = document.createElement('tr'); tr.id = 'fila-' + id;
        tr.innerHTML = `<td><input type="hidden" name="productos[${idx}][id]" value="${id}"><strong>${nombre}</strong><br><small style="color:#999;">Stock: ${stock}</small></td>
            <td><input type="number" name="productos[${idx}][cantidad]" value="1" min="1" max="${stock}" class="form-control form-control-sm cant" style="width:65px;" onchange="recalc('${id}')"></td>
            <td>{{ $empresa->simbolo_moneda ?? '$' }} ${precio.toFixed(2)}</td>
            <td><input type="number" name="productos[${idx}][descuento]" value="0" min="0" step="0.01" class="form-control form-control-sm" style="width:80px;" onchange="recalc('${id}')"></td>
            <td id="sub-${id}"><strong>{{ $empresa->simbolo_moneda ?? '$' }} ${precio.toFixed(2)}</strong></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="quitar('${id}')"><i class="fas fa-times"></i></button></td>`;
        cuerpo.appendChild(tr); idx++;
    }
    selProd.selectedIndex = 0; totales();
}

function recalc(id) {
    let tr = document.getElementById('fila-' + id);
    if (!tr) return;
    let cant = parseInt(tr.querySelector('.cant').value) || 0;
    let desc = parseFloat(tr.querySelectorAll('input')[2].value) || 0;
    document.getElementById('sub-' + id).innerHTML = '<strong>{{ $empresa->simbolo_moneda ?? '$' }} ' + Math.max((items[id].precio * cant) - desc, 0).toFixed(2) + '</strong>';
    totales();
}

function quitar(id) {
    let tr = document.getElementById('fila-' + id);
    if (tr) tr.remove();
    delete items[id];
    if (Object.keys(items).length === 0) vacio.style.display = '';
    totales();
}

let cuponAplicado = null;

function totales() {
    let sub = 0;
    Object.keys(items).forEach(id => {
        let tr = document.getElementById('fila-' + id);
        if (!tr) return;
        let cant = parseInt(tr.querySelector('.cant').value) || 0;
        let desc = parseFloat(tr.querySelectorAll('input')[2].value) || 0;
        sub += (items[id].precio * cant) - desc;
    });
    let dg = parseFloat(document.getElementById('descGen').value) || 0;
    let descCupon = 0;
    if (cuponAplicado) {
        if (cuponAplicado.tipo === 'porcentaje') {
            descCupon = sub * (cuponAplicado.valor / 100);
        } else {
            descCupon = cuponAplicado.valor;
        }
    }
    let base = Math.max(sub - dg - descCupon, 0);
    let simbolo = '{{ $empresa->simbolo_moneda ?? '$' }}';
    document.getElementById('lblSubtotal').textContent = simbolo + ' ' + sub.toFixed(2);
    document.getElementById('lblDescuento').textContent = '— ' + simbolo + ' ' + dg.toFixed(2);
    if (cuponAplicado) {
        document.getElementById('cuponRow').style.display = 'flex';
        document.getElementById('lblCuponCodigo').textContent = cuponAplicado.codigo;
        document.getElementById('lblCuponDescuento').textContent = '— ' + simbolo + ' ' + descCupon.toFixed(2);
    } else {
        document.getElementById('cuponRow').style.display = 'none';
    }
    let igvPct = {{ $empresa->igv ?? 18 }};
    @if(($empresa->pais ?? '') === 'CL')
    // 🇨🇱 Chile: el precio YA INCLUYE IVA → descomponer
    let valorCL = base;
    let netoCL = valorCL / (1 + igvPct / 100);
    let ivaCL = valorCL - netoCL;
    document.getElementById('lblNeto').textContent = simbolo + ' ' + netoCL.toFixed(2);
    document.getElementById('lblIgv').textContent = simbolo + ' ' + ivaCL.toFixed(2);
    document.getElementById('lblTotal').textContent = simbolo + ' ' + valorCL.toFixed(2);
    document.getElementById('lblTotalMini').textContent = simbolo + ' ' + valorCL.toFixed(2);
    window.totalActual = valorCL;
    @else
    // Otros países: el impuesto se SUMA al precio
    document.getElementById('lblIgv').textContent = simbolo + ' ' + (base * (igvPct / 100)).toFixed(2);
    document.getElementById('lblTotal').textContent = simbolo + ' ' + (base * (1 + igvPct / 100)).toFixed(2);
    document.getElementById('lblTotalMini').textContent = simbolo + ' ' + (base * (1 + igvPct / 100)).toFixed(2);
    window.totalActual = base * (1 + igvPct / 100);
    @endif
    btnReg.disabled = Object.keys(items).length === 0;
    var bp = document.getElementById('btnPoint');
    if (bp) bp.disabled = Object.keys(items).length === 0;
    if (typeof actualizarVuelto === 'function') actualizarVuelto();
}

// ── Efectivo recibido y vuelto (solo pago en efectivo) ──
function actualizarVuelto() {
    const row = document.getElementById('rowEfectivo');
    if (!row || row.style.display === 'none') return;

    const total = window.totalActual || 0;
    const recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
    const lblVuelto = document.getElementById('lblVuelto');
    const lblFalta = document.getElementById('lblFalta');
    const simbolo = '{{ $empresa->simbolo_moneda ?? '$' }}';

    if (recibido <= 0 || total <= 0) {
        lblVuelto.style.display = 'none';
        lblFalta.style.display = 'none';
        return;
    }

    const dif = recibido - total;
    if (dif >= 0) {
        lblVuelto.style.display = 'block';
        lblVuelto.textContent = 'VUELTO: ' + simbolo + ' ' + dif.toLocaleString('es-CL', { maximumFractionDigits: 2 });
        lblFalta.style.display = 'none';
    } else {
        lblVuelto.style.display = 'none';
        lblFalta.style.display = 'block';
        lblFalta.textContent = 'Faltan ' + simbolo + ' ' + Math.abs(dif).toLocaleString('es-CL', { maximumFractionDigits: 2 }) + ' para completar el total.';
    }
}

function setRecibido(valor) {
    document.getElementById('montoRecibido').value = valor;
    actualizarVuelto();
}

document.getElementById('metodo_pago').addEventListener('change', function () {
    toggleCamposPago(this.value);
    actualizarVuelto();
});

function toggleCamposPago(metodo) {
    document.getElementById('rowEfectivo').style.display = (metodo === 'efectivo') ? 'block' : 'none';
    let conRef = (metodo === 'transferencia');
    let rowRef = document.getElementById('rowReferencia');
    if (rowRef) {
        rowRef.style.display = conRef ? 'block' : 'none';
        let hint = document.getElementById('referenciaHint');
        if (hint) {
            hint.textContent = 'N° de transferencia o comprobante.';
        }
        if (!conRef) {
            let inp = document.getElementById('referenciaPago');
            if (inp) inp.value = '';
        } else {
            let inp = document.getElementById('referenciaPago');
            if (inp) inp.focus();
        }
    }
    // Flujo pagina-web: el método se elige ANTES con las píldoras.
    // Efectivo → caja con vuelto; Tarjeta → completada directo;
    // Mercado Pago → pendiente para cobrar en el detalle (Point o QR).
}

// Píldoras de método de pago (diseño elegante, mismo valor que el select)
document.querySelectorAll('#payPills .pay-pill').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('#payPills .pay-pill').forEach(function(b){ b.classList.remove('active','btn-success','btn-primary'); });
        this.classList.add('active');
        var sel = document.getElementById('metodo_pago');
        sel.value = this.dataset.val;
        sel.dispatchEvent(new Event('change'));
    });
});

function validarCupon() {
    let codigo = document.getElementById('cuponInput').value.trim().toUpperCase();
    let msg = document.getElementById('cuponMsg');
    let btn = document.getElementById('btnValidarCupon');

    if (!codigo) {
        msg.innerHTML = '<span class="text-warning">Ingresa un código de cupón</span>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Validando...';
    msg.innerHTML = '';

    fetch('{{ route("api.cupon.validar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ codigo: codigo })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cuponAplicado = data.cupon;
            document.getElementById('cuponInput').value = data.cupon.codigo;
            msg.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Cupón válido: ' +
                (data.cupon.tipo === 'porcentaje' ? data.cupon.valor + '% de descuento' : '{{ $empresa->simbolo_moneda ?? '$' }} ' + data.cupon.valor + ' de descuento') + '</span>';
            totales();
        } else {
            cuponAplicado = null;
            msg.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>' + data.message + '</span>';
            totales();
        }
    })
    .catch(err => {
        cuponAplicado = null;
        msg.innerHTML = '<span class="text-danger">Error al validar el cupón</span>';
        totales();
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Validar';
    });
}

// Validar cupón al presionar Enter en el campo
document.getElementById('cuponInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        validarCupon();
    }
});

// ===== ESCÁNER CON CÁMARA (ZXing - lee códigos de barras y QR) =====
let codeReader = null;
let videoStream = null;

function iniciarScanner() {
    let container = document.getElementById('scannerContainer');
    if (scannerActivo) { 
        detenerScanner();
        return; 
    }
    
    document.getElementById('scannerMensaje').innerHTML = 'Preparando escáner...';
    container.style.display = 'block';
    scannerActivo = true;
    
    // Cargar ZXing si no está cargado
    if (typeof ZXing === 'undefined') {
        cargarZXing(function() {
            iniciarEscaneoEnVivo();
        });
    } else {
        iniciarEscaneoEnVivo();
    }
}

// Cargar librería ZXing
function cargarZXing(callback) {
    let script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/@zxing/library@0.20.0/umd/index.min.js';
    script.onload = function() { callback(); };
    script.onerror = function() {
        alert('No se pudo cargar la librería de lectura de códigos.');
        document.getElementById('scannerContainer').style.display = 'none';
        scannerActivo = false;
    };
    document.head.appendChild(script);
}

// Escaneo en tiempo real con ZXing (lee códigos de barras 1D y QR)
function iniciarEscaneoEnVivo() {
    let videoWrap = document.getElementById('scannerVideoWrap');
    let video = document.getElementById('scannerVideo');
    let mensaje = document.getElementById('scannerMensaje');
    
    videoWrap.style.display = 'block';
    mensaje.innerHTML = '<i class="fas fa-camera fa-2x mb-2" style="color:#0891b2;"></i><br>Apuntando la cámara al código...<br><small style="color:#6b7280;">Alinea el código dentro del recuadro morado</small>';
    
    // Solicitar acceso a la cámara trasera
    navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' } },
        audio: false
    }).then(function(stream) {
        videoStream = stream;
        video.srcObject = stream;
        video.play();
        
        // Crear lector ZXing
        codeReader = new ZXing.BrowserMultiFormatReader();
        
        // Escanear continuamente desde el video (ZXing maneja el bucle internamente)
        codeReader.decodeFromVideoElement(video, function(result, err) {
            if (result && result.getText()) {
                let codigo = result.getText().trim();
                // Feedback visual: recuadro verde + vibración
                document.getElementById('scannerBox').style.borderColor = '#22c55e';
                if (navigator.vibrate) navigator.vibrate(200);
                mensaje.innerHTML = '<i class="fas fa-check-circle fa-2x mb-2" style="color:#22c55e;"></i><br>Código leído: <strong>' + codigo + '</strong>';
                
                // Agregar producto
                document.getElementById('codBarras').value = codigo;
                buscarCodigo(codigo);
                
                // Detener escáner
                setTimeout(function() {
                    detenerScanner();
                }, 500);
            }
        });
        
    }).catch(function(err) {
        console.error('Error cámara:', err);
        // Si no hay cámara en vivo, usar foto
        mensaje.innerHTML = 'Cámara en vivo no disponible. Tomando foto del código...';
        videoWrap.style.display = 'none';
        document.getElementById('scannerInput').click();
    });
}

function procesarFotoCodigo(input) {
    let container = document.getElementById('scannerContainer');
    if (!input.files || !input.files[0]) {
        container.style.display = 'none';
        scannerActivo = false;
        return;
    }
    
    document.getElementById('scannerMensaje').innerHTML = 'Leyendo código de la foto...';
    
    let file = input.files[0];
    let reader = new FileReader();
    
    reader.onload = function(e) {
        let img = new Image();
        img.onload = function() {
            // Usar BarcodeDetector API nativa del navegador si está disponible
            if ('BarcodeDetector' in window) {
                leerConBarcodeDetector(img, container);
            } else {
                // Fallback: cargar librería ZXing para leer códigos de barras
                if (typeof ZXing === 'undefined') {
                    cargarZXing(function() {
                        leerConZXing(img, container);
                    });
                } else {
                    leerConZXing(img, container);
                }
            }
        };
        img.src = e.target.result;
    };
    
    reader.readAsDataURL(file);
    input.value = '';
}

// Leer código con BarcodeDetector (API nativa del navegador)
function leerConBarcodeDetector(img, container) {
    try {
        const detector = new BarcodeDetector({
            formats: ['code_128', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_39', 'code_93', 'codabar', 'itf', 'qr_code']
        });
        detector.detect(img).then(codes => {
            if (codes && codes.length > 0 && codes[0].rawValue) {
                let codigo = codes[0].rawValue.trim();
                document.getElementById('codBarras').value = codigo;
                buscarCodigo(codigo);
                container.style.display = 'none';
                scannerActivo = false;
            } else {
                fallbackALeerConZXing(img, container);
            }
        }).catch(function(err) {
            fallbackALeerConZXing(img, container);
        });
    } catch(e) {
        fallbackALeerConZXing(img, container);
    }
}

// Si BarcodeDetector no lee, intentar con ZXing
function fallbackALeerConZXing(img, container) {
    if (typeof ZXing === 'undefined') {
        cargarZXing(function() {
            leerConZXing(img, container);
        });
    } else {
        leerConZXing(img, container);
    }
}

// Leer código con ZXing desde imagen
function leerConZXing(img, container) {
    try {
        const codeReaderImg = new ZXing.BrowserMultiFormatReader();
        codeReaderImg.decodeFromImageElement(img).then(result => {
            if (result && result.getText()) {
                let codigo = result.getText().trim();
                document.getElementById('codBarras').value = codigo;
                buscarCodigo(codigo);
                container.style.display = 'none';
                scannerActivo = false;
            } else {
                alert('No se pudo leer el código. Acerca la cámara al código, con buena luz y sosteniendo el teléfono quieto.');
                container.style.display = 'none';
                scannerActivo = false;
            }
        }).catch(function(err) {
            alert('No se pudo leer el código. Asegúrate de que el código esté completo, enfocado y con buena luz.');
            container.style.display = 'none';
            scannerActivo = false;
        });
    } catch(e) {
        console.error('Error ZXing:', e);
        alert('No se pudo leer el código. Intenta de nuevo con mejor enfoque y luz.');
        container.style.display = 'none';
        scannerActivo = false;
    }
}

function detenerScanner() {
    document.getElementById('scannerContainer').style.display = 'none';
    scannerActivo = false;
    
    // Detener video
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        videoStream = null;
    }
    
    // Detener lector ZXing
    if (codeReader) {
        try { codeReader.reset(); } catch(e) {}
        codeReader = null;
    }
    
    // Restablecer recuadro
    let box = document.getElementById('scannerBox');
    if (box) box.style.borderColor = '#0891b2';
    
    // Ocultar video
    let videoWrap = document.getElementById('scannerVideoWrap');
    if (videoWrap) videoWrap.style.display = 'none';
}
</script>
@endpush
