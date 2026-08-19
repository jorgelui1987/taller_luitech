@extends('layouts.app')
@section('title', 'Nueva Venta')
@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('ventas.index') }}" style="color:#a855f7;">Ventas</a></li></ul>
    <ul><li class="breadcrumb-item active">Nueva Venta</li></ul>
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
                        <label for="clienteSelect" class="form-label">Cliente <span class="text-muted">(opcional)</span></label>
                        <select name="cliente_id" id="clienteSelect" class="form-select">
                            <option value="">— Sin cliente (venta general) —</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre_completo }} — {{ $c->telefono }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <span class="form-label d-block">Agregar Producto</span>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search fa-sm"></i></span>
                                    <label for="buscador" class="visually-hidden">Buscar producto</label>
                                    <input type="text" id="buscador" class="form-control" placeholder="Buscar nombre o código..." oninput="filtrar()">
                                    <label for="selectProd" class="visually-hidden">Seleccionar producto</label>
                                    <select id="selectProd" class="form-select">
                                        <option value="">— Seleccionar —</option>
                                        @foreach($productos as $p)
                                            <option value="{{ $p->id }}" data-precio="{{ $p->precio_venta }}" data-stock="{{ $p->stock }}" data-codigo="{{ $p->codigo }}" data-codigo_barras="{{ $p->codigo_barras ?? '' }}">{{ $p->nombre }} ({{ $p->stock }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="codBarras" class="visually-hidden">Código de barras</label>
                                <input type="text" id="codBarras" class="form-control" placeholder="📷 Escanea código de barras..." style="font-family:monospace;" onkeydown="if(event.key==='Enter'){event.preventDefault();buscarCodigo(this.value);}">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" onclick="agregar()"><i class="fas fa-plus me-1"></i>Agregar</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="iniciarScanner()"><i class="fas fa-camera me-1"></i>Escanear</button>
                            </div>
                        </div>
                        <input type="file" id="scannerInput" accept="image/*" capture="environment" style="display:none;" onchange="procesarFotoCodigo(this)">
                        <div id="scannerContainer" style="display:none; max-width:400px; margin-top:8px;" class="card p-2">
                            <div id="scannerMensaje" style="font-size:13px; padding:10px; text-align:center; color:#6b7280;">
                                <i class="fas fa-camera fa-2x mb-2" style="color:#a855f7;"></i><br>
                                Preparando escáner...
                            </div>
                            <div id="scannerVideoWrap" style="position:relative; display:none;">
                                <video id="scannerVideo" playsinline muted style="width:100%; border-radius:8px; background:#000;"></video>
                                <div id="scannerOverlay" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;">
                                    <div id="scannerBox" style="position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:80%; height:60px; border:3px solid #a855f7; border-radius:8px; box-shadow:0 0 0 9999px rgba(0,0,0,0.4);"></div>
                                    <div id="scannerLine" style="position:absolute; left:10%; width:80%; height:2px; background:#22c55e; box-shadow:0 0 8px #22c55e; animation:scanline 2s linear infinite;"></div>
                                </div>
                            </div>
                            <style>
                                @keyframes scanline {
                                    0% { top: 20%; }
                                    50% { top: 80%; }
                                    100% { top: 20%; }
                                }
                            </style>
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
                            <label for="metodo_pago" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago" id="metodo_pago" class="form-select" required>
                                <option value="efectivo">💵 Efectivo</option>
                                <option value="tarjeta">💳 Tarjeta</option>
                                <option value="transferencia">🏦 Transferencia</option>
                                @if(($empresa->mercadopago_activo ?? false))
                                <option value="mercadopago">🟦 Mercado Pago</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="descGen" class="form-label">Descuento General</label>
                            <input type="number" class="form-control" name="descuento_general" id="descGen" min="0" step="0.01" value="0" oninput="totales()">
                        </div>
                        <div class="col-md-6">
                            <label for="cuponInput" class="form-label">Cupón de Descuento</label>
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
                            <label for="notas" class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" id="notas" rows="2"></textarea>
                        </div>
                    </div>
                    <hr>
                    @if(($empresa->pais ?? '') === 'CL')
                    <div class="d-flex justify-content-between mb-2"><span>Valor</span><span id="lblSubtotal">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Neto</span><span id="lblNeto">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    @else
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span id="lblSubtotal">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    @endif
                    <div class="d-flex justify-content-between mb-2"><span>Descuento</span><span id="lblDescuento" class="text-danger">— {{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    <div class="d-flex justify-content-between mb-2" id="cuponRow" style="display:none;">
                        <span>Cupón <span id="lblCuponCodigo" class="text-success"></span></span>
                        <span id="lblCuponDescuento" class="text-danger">— {{ $empresa->simbolo_moneda ?? '$' }} 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span>{{ $empresa->pais == 'CL' ? 'IVA' : 'IGV' }} ({{ $empresa->igv ?? 18 }}%)</span><span id="lblIgv">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3"><strong>Total</strong><span id="lblTotal" style="font-size:20px;font-weight:700;color:#a855f7;">{{ $empresa->simbolo_moneda ?? '$' }} 0.00</span></div>
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
    @else
    // Otros países: el impuesto se SUMA al precio
    document.getElementById('lblIgv').textContent = simbolo + ' ' + (base * (igvPct / 100)).toFixed(2);
    document.getElementById('lblTotal').textContent = simbolo + ' ' + (base * (1 + igvPct / 100)).toFixed(2);
    @endif
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
    mensaje.innerHTML = '<i class="fas fa-camera fa-2x mb-2" style="color:#a855f7;"></i><br>Apuntando la cámara al código...<br><small style="color:#6b7280;">Alinea el código dentro del recuadro morado</small>';
    
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
    if (box) box.style.borderColor = '#a855f7';
    
    // Ocultar video
    let videoWrap = document.getElementById('scannerVideoWrap');
    if (videoWrap) videoWrap.style.display = 'none';
}
</script>
@endpush
