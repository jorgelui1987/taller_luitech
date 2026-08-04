@extends('layouts.app')
@section('title', 'Nueva Venta')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}" style="color:#a855f7;">Ventas</a></li>
    <li class="breadcrumb-item active">Nueva Venta</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Registrar Venta</h5>
                <p class="text-muted mb-4" style="font-size:13px;">Agrega productos y completa los datos</p>
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
                    <div class="mb-4">
                        <label class="form-label">Cliente <span class="text-muted">(opcional)</span></label>
                        <select name="cliente_id" id="clienteSelect" class="form-select">
                            <option value="">— Sin cliente (venta general) —</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre_completo }} — {{ $c->telefono }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Agregar Producto</label>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search fa-sm"></i></span>
                                    <input type="text" id="buscador" class="form-control" placeholder="Buscar nombre o código..." oninput="filtrar()">
                                    <select id="selectProd" class="form-select">
                                        <option value="">— Seleccionar —</option>
                                        @foreach($productos as $p)
                                            <option value="{{ $p->id }}" data-precio="{{ $p->precio_venta }}" data-stock="{{ $p->stock }}" data-codigo="{{ $p->codigo }}" data-codigo_barras="{{ $p->codigo_barras ?? '' }}">{{ $p->nombre }} ({{ $p->stock }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input type="text" id="codBarras" class="form-control" placeholder="📷 Escanea código de barras..." style="font-family:monospace;" onkeydown="if(event.key==='Enter'){event.preventDefault();buscarCodigo(this.value);}">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" onclick="agregar()"><i class="fas fa-plus me-1"></i>Agregar</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="iniciarScanner()"><i class="fas fa-camera me-1"></i>Escanear</button>
                            </div>
                        </div>
                        <div id="scannerContainer" style="display:none; max-width:400px; margin-top:8px;" class="card p-2">
                            <div id="qrReader" style="width:100%; min-height:200px;"></div>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="detenerScanner()"><i class="fas fa-times me-1"></i>Cerrar</button>
                        </div>
                    </div>
                    <div class="table-responsive mb-3">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Producto</th><th style="width:80px;">Cant.</th><th style="width:110px;">Precio</th><th style="width:100px;">Dto.</th><th style="width:110px;">Subtotal</th><th style="width:40px;"></th></tr></thead>
                            <tbody id="cuerpo">
                                <tr id="vacio"><td colspan="6" class="text-center text-muted py-4">Agrega productos a la venta</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago" class="form-select" required>
                                <option value="efectivo">💵 Efectivo</option>
                                <option value="tarjeta">💳 Tarjeta</option>
                                <option value="transferencia">🏦 Transferencia</option>
                                
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Descuento General</label>
                            <input type="number" class="form-control" name="descuento_general" id="descGen" min="0" step="0.01" value="0" oninput="totales()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cupón de Descuento</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="cupon_codigo" id="cuponInput" 
                                       placeholder="Ingresa el código del cupón (ej: CUP-ABC123-456)" 
                                       style="text-transform:uppercase;" autocomplete="off">
                                <button type="button" class="btn btn-outline-success" id="btnValidarCupon" onclick="validarCupon()">
                                    <i class="fas fa-check me-1"></i>Validar
                                </button>
                            </div>
                            <div id="cuponMsg" class="form-text mt-1" style="font-size:12px;"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" rows="2"></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span id="lblSubtotal">S/ 0.00</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Descuento</span><span id="lblDescuento" class="text-danger">— S/ 0.00</span></div>
                    <div class="d-flex justify-content-between mb-2" id="cuponRow" style="display:none;">
                        <span>Cupón <span id="lblCuponCodigo" class="text-success"></span></span>
                        <span id="lblCuponDescuento" class="text-danger">— S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span>IGV ({{ $empresa->igv ?? 18 }}%)</span><span id="lblIgv">S/ 0.00</span></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3"><strong>Total</strong><span id="lblTotal" style="font-size:20px;font-weight:700;color:#a855f7;">S/ 0.00</span></div>
                    <button type="submit" class="btn btn-primary w-100 py-2" id="btnReg" disabled><i class="fas fa-cash-register me-2"></i>Registrar Venta</button>
                </form>
            </div>
        </div>
    </div>
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

function filtrar() {
    let q = document.getElementById('buscador').value.toLowerCase();
    for (let i = 1; i < selProd.options.length; i++) {
        selProd.options[i].hidden = !selProd.options[i].text.toLowerCase().includes(q);
    }
}

function buscarCodigo(cod) {
    cod = cod.trim();
    if (!cod) return;
    // Buscar primero por código de barras exacto, luego por código SKU, luego por nombre
    for (let i = 1; i < selProd.options.length; i++) {
        let opt = selProd.options[i];
        let cb = (opt.dataset.codigo_barras || '').toLowerCase();
        let sku = (opt.dataset.codigo || '').toLowerCase();
        let nom = opt.text.toLowerCase();
        let q = cod.toLowerCase();
        if (cb === q || sku === q || nom.includes(q)) {
            selProd.selectedIndex = i;
            agregar();
            document.getElementById('codBarras').value = '';
            return;
        }
    }
    alert('Producto no encontrado con código: ' + cod);
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
            <td>S/ ${precio.toFixed(2)}</td>
            <td><input type="number" name="productos[${idx}][descuento]" value="0" min="0" step="0.01" class="form-control form-control-sm" style="width:80px;" onchange="recalc('${id}')"></td>
            <td id="sub-${id}"><strong>S/ ${precio.toFixed(2)}</strong></td>
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
    document.getElementById('sub-' + id).innerHTML = '<strong>S/ ' + Math.max((items[id].precio * cant) - desc, 0).toFixed(2) + '</strong>';
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
    document.getElementById('lblSubtotal').textContent = 'S/ ' + sub.toFixed(2);
    document.getElementById('lblDescuento').textContent = '— S/ ' + dg.toFixed(2);
    if (cuponAplicado) {
        document.getElementById('cuponRow').style.display = 'flex';
        document.getElementById('lblCuponCodigo').textContent = cuponAplicado.codigo;
        document.getElementById('lblCuponDescuento').textContent = '— S/ ' + descCupon.toFixed(2);
    } else {
        document.getElementById('cuponRow').style.display = 'none';
    }
    let igvPct = {{ $empresa->igv ?? 18 }};
    document.getElementById('lblIgv').textContent = 'S/ ' + (base * (igvPct / 100)).toFixed(2);
    document.getElementById('lblTotal').textContent = 'S/ ' + (base * (1 + igvPct / 100)).toFixed(2);
    btnReg.disabled = Object.keys(items).length === 0;
}

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
                (data.cupon.tipo === 'porcentaje' ? data.cupon.valor + '% de descuento' : 'S/ ' + data.cupon.valor + ' de descuento') + '</span>';
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

function iniciarScanner() {
    let container = document.getElementById('scannerContainer');
    if (scannerActivo) { container.style.display = 'none'; scannerActivo = false; return; }
    container.style.display = 'block';
    scannerActivo = true;
    let script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
    script.onload = function() {
        let qr = new Html5Qrcode("qrReader");
        qr.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 150 } },
            function(text) {
                document.getElementById('codBarras').value = text;
                buscarCodigo(text);
                qr.stop().catch(()=>{});
                container.style.display = 'none';
                scannerActivo = false;
            }
        ).catch(function(err) {
            alert('Cámara no disponible: ' + err);
            container.style.display = 'none';
            scannerActivo = false;
        });
    };
    document.head.appendChild(script);
}

function detenerScanner() {
    document.getElementById('scannerContainer').style.display = 'none';
    scannerActivo = false;
}
</script>
@endpush