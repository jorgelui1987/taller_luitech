@extends('layouts.app')
@section('title', 'Nueva Orden de Trabajo')
@section('breadcrumb')
<ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}">Reparaciones</a></li></ul>
<ul><li class="breadcrumb-item active">Nueva Orden</li></ul>
@endsection
@push('styles')
<style>
.ot-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;}
.ot-sec-title{font-size:13px;font-weight:800;color:#0f172a;margin-bottom:12px;}
.ot-label{font-size:12.5px;font-weight:600;color:#374151;margin-bottom:4px;display:block;}
.ot-total{background:#0f172a;color:#fff;border-radius:12px;padding:12px 16px;}
.signature-pad-wrapper{border:2px dashed #d1d5db;border-radius:12px;background:#fff;position:relative;cursor:crosshair;}
.signature-pad-wrapper canvas{display:block;width:100%;height:140px;touch-action:none;}
.foto-preview-item{border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;position:relative;}
.foto-preview-item img{width:100%;height:90px;object-fit:cover;}
.foto-preview-item .btn-remove{position:absolute;top:4px;right:4px;width:24px;height:24px;border-radius:50%;background:#ef4444;color:#fff;border:none;font-size:10px;}
</style>
@endpush
@section('content')
<div class="row justify-content-center"><div class="col-lg-11">
<div class="d-flex justify-content-between mb-3">
<div><h5 class="fw-bold mb-1">Ingresar Nueva Orden de Trabajo</h5>
<p class="text-muted mb-0" style="font-size:13px;">Todo en una pantalla</p></div>
<a href="{{ route('reparaciones.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
</div>
@if($errors->any())
<div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('reparaciones.store') }}" method="POST" enctype="multipart/form-data" id="orderCreateForm">
@csrf
<div class="row g-3">
<div class="col-lg-7">
<div class="ot-card p-3 mb-3">
<div class="ot-sec-title">Cliente y técnico</div>
<div class="row g-2">
<div class="col-md-6"><label class="ot-label">Código (opcional)</label>
<input type="text" class="form-control form-control-sm" placeholder="Vacío = automático" disabled></div>
<div class="col-md-6"><label class="ot-label">Fecha Ingreso</label>
<input type="text" class="form-control form-control-sm" value="{{ now()->format('d-m-Y') }}" disabled></div>
<div class="col-12"><label class="ot-label">Nombre del Cliente *</label>
<input type="text" id="clienteSearch" class="form-control" placeholder="Ej. Andrés Silva" autocomplete="off">
<input type="hidden" name="cliente_id" id="clienteId" required>
<div id="clienteResultados" class="list-group mt-1"></div>
<div class="form-text" style="font-size:11px;"><span id="clienteSeleccionadoInfo" class="text-success fw-bold"></span>
<button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#modalClienteRapido">+ Nuevo cliente</button></div></div>
<div class="col-md-6"><label class="ot-label">Técnico *</label>
<select name="tecnico_id" class="form-select form-select-sm" required>
<option value="">Por Asignar</option>
@foreach($tecnicos as $tec)<option value="{{ $tec->id }}">{{ $tec->name }}</option>@endforeach
</select></div>
<div class="col-md-6"><label class="ot-label">Prioridad *</label>
<select name="prioridad" class="form-select form-select-sm" required>
<option value="baja">Baja</option><option value="media" selected>Media</option><option value="alta">Alta</option><option value="urgente">Urgente</option>
</select></div>
</div></div>
<div class="ot-card p-3 mb-3">
<div class="ot-sec-title">Equipo / Modelo</div>
<div class="row g-2">
<div class="col-md-6"><label class="ot-label">Tipo *</label>
<select name="tipo_dispositivo" class="form-select form-select-sm" required>
<option value="celular" selected>Celular</option><option value="tablet">Tablet</option><option value="portatil">Portátil</option><option value="otros">Otro</option>
</select></div>
<div class="col-md-6"><label class="ot-label">Equipo / Modelo</label>
<input type="text" name="dispositivo" class="form-control form-control-sm" placeholder="Ej. iPhone 14 Pro Max" maxlength="150"></div>
<div class="col-md-4"><label class="ot-label">Marca</label>
<input type="text" name="marca" class="form-control form-control-sm" placeholder="Ej. Apple" maxlength="80"></div>
<div class="col-md-4"><label class="ot-label">Modelo</label>
<input type="text" name="modelo" class="form-control form-control-sm" placeholder="Ej. A57" maxlength="100"></div>
<div class="col-md-4"><label class="ot-label">IMEI</label>
<input type="text" name="imei" class="form-control form-control-sm" placeholder="15 dígitos" maxlength="20"></div>
<div class="col-md-6"><label class="ot-label">Desbloqueo</label>
<select name="tipo_codigo" id="tipoCodigoSelect" class="form-select form-select-sm">
<option value="">Sin código</option><option value="pin">PIN numérico</option><option value="patron">Dibujar patrón</option>
</select></div>
<div class="col-md-6" id="pinWrapper" style="display:none;"><label class="ot-label">PIN numérico</label>
<input type="text" name="codigo_equipo" class="form-control form-control-sm" placeholder="Ej. 1234" maxlength="80"></div>
</div></div>
<div class="ot-card p-3 mb-3">
<div class="ot-sec-title">Detalle de la Falla *</div>
<textarea name="falla_reportada" class="form-control" rows="2" required placeholder="Ej. Cambio de batería y calibración"></textarea>
<label class="ot-label mt-2">Observaciones de recepción (estado físico)</label>
<textarea name="notas" class="form-control" rows="2" placeholder="Ej. Rayón en esquina inferior derecha"></textarea>
<label class="ot-label mt-2">Accesorios entregados por el cliente</label>
<input type="text" name="accesorios_entregados" class="form-control form-control-sm" placeholder="Ej. Cargador original, funda negra" maxlength="255">
</div>
</div>


<div class="col-lg-5">
<div class="ot-card p-3 mb-3">
<div class="ot-sec-title">Presupuesto de la reparación</div>
<div class="row g-2">
<div class="col-6"><label class="ot-label">Precio al cliente ($)</label>
<input type="number" name="presupuesto" id="presupuestoInput" class="form-control" value="0" min="0" step="0.01"></div>
<div class="col-6"><label class="ot-label">Abono ($)</label>
<input type="number" name="abono" id="abonoInput" class="form-control" value="0" min="0" step="0.01"></div>
<div class="col-6"><label class="ot-label">Costo real del repuesto ($)</label>
<input type="number" name="costo_repuesto" id="costoRepuestoInput" class="form-control" value="0" min="0" step="0.01"></div>
<div class="col-6"><label class="ot-label">Cupón</label>
<input type="text" name="cupon_codigo" class="form-control form-control-sm" maxlength="30"></div>
<div class="col-12"><div class="ot-total d-flex justify-content-between align-items-center">
<span style="font-size:13px;">Total a cobrar</span><b id="totalPreview" style="font-size:22px;">$ 0</b>
</div></div>
<div class="col-md-6"><label class="ot-label">Garantía</label>
<select id="garantiaSelect" class="form-select form-select-sm"><option value="0">Sin garantía</option><option value="1">Con garantía</option></select></div>
<div class="col-md-6"><label class="ot-label">Días garantía</label>
<input type="number" name="dias_garantia" class="form-control form-control-sm" value="30" min="0" max="365"></div>
<div class="col-12"><div class="form-check">
<input class="form-check-input" type="checkbox" name="registrar_egreso_repuesto" id="egresoCheck" value="1">
<label class="form-check-label" for="egresoCheck" style="font-size:12.5px;">Ya pagué la pieza: registrar <b>Egreso</b> en caja</label>
</div><input type="hidden" name="garantia" id="garantiaHidden" value="0"></div>
</div></div>
<div class="ot-card p-3 mb-3">
<div class="ot-sec-title">Fotos de respaldo (máx. 6)</div>
<button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="document.getElementById('inputFotosEvidencia').click();">Tomar / elegir fotos</button>
<input type="file" id="inputFotosEvidencia" name="fotos[]" accept="image/*" multiple class="d-none">
<div id="galeriaEvidenciasPrevias" class="row g-2"></div>
<div id="contenedorTiposFotos"></div>
<small class="text-muted" id="fotoContador">Ningún archivo seleccionado</small>
</div>
<div class="ot-card p-3 mb-3">
<div class="ot-sec-title">Firma del cliente (dedo o mouse)</div>
<div class="signature-pad-wrapper">
<canvas id="canvasFirmaRecepcionCreate"></canvas>
<span class="placeholder" id="placeholderFirmaCreate">Firme aquí</span>
</div>
<input type="hidden" name="firma_recepcion_data" id="firmaRecepcionDataInput">
<div class="d-flex justify-content-between align-items-center mt-2">
<button type="button" class="btn btn-link btn-sm p-0" onclick="limpiarFirmaCreate();">Limpiar firma</button>
<button type="submit" class="btn btn-success fw-bold px-4" id="btnCrearOrden">Crear Orden</button>
</div></div>
</div>
</div>
</form>
</div></div>
        <div class="modal fade" id="modalClienteRapido" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
                <div class="modal-header"><h6 class="modal-title fw-bold">Nuevo Cliente</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><div class="row g-2">
                    <div class="col-6"><input type="text" class="form-control form-control-sm" id="crNombre" placeholder="Nombre *"></div>
                    <div class="col-6"><input type="text" class="form-control form-control-sm" id="crApellido" placeholder="Apellido *"></div>
                    <div class="col-6"><input type="text" class="form-control form-control-sm" id="crTelefono" placeholder="Teléfono *"></div>
                    <div class="col-6"><input type="text" class="form-control form-control-sm" id="crDni" placeholder="DNI"></div>
                </div><div id="crMsg" class="mt-2" style="display:none;"></div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success btn-sm" id="crGuardar">Guardar</button>
                </div>
            </div></div>
        </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
let signaturePadCreate = null;
function initSignaturePadCreate() {
    const c = document.getElementById('canvasFirmaRecepcionCreate');
    if (!c) return;
    const r = Math.max(window.devicePixelRatio || 1, 1);
    const w = c.parentElement.clientWidth || 300;
    c.width = w * r; c.height = 140 * r;
    c.getContext('2d').setTransform(r, 0, 0, r, 0, 0);
    signaturePadCreate = new SignaturePad(c, { backgroundColor: 'rgb(255,255,255)', penColor: 'rgb(15,23,42)' });
}
function limpiarFirmaCreate() {
    if (signaturePadCreate) signaturePadCreate.clear();
    const i = document.getElementById('firmaRecepcionDataInput');
    if (i) i.value = '';
}
let listaFotosEvidencia = [];
function renderFotos() {
    const g = document.getElementById('galeriaEvidenciasPrevias');
    const fi = document.getElementById('inputFotosEvidencia');
    const tc = document.getElementById('contenedorTiposFotos');
    const cnt = document.getElementById('fotoContador');
    if (!g) return;
    g.innerHTML = '';
    if (tc) tc.innerHTML = '';
    const dt = new DataTransfer();
    listaFotosEvidencia.forEach((f) => {
        const col = document.createElement('div');
        col.className = 'col-4';
        col.innerHTML = '<div class="foto-preview-item"><img alt="Foto"><button type="button" class="btn-remove">x</button></div>';
        col.querySelector('img').src = URL.createObjectURL(f.file);
        col.querySelector('.btn-remove').addEventListener('click', () => {
            listaFotosEvidencia = listaFotosEvidencia.filter(x => x.id !== f.id);
            renderFotos();
        });
        g.appendChild(col);
        if (tc) {
            const h = document.createElement('input');
            h.type = 'hidden'; h.name = 'fotos_tipos[]'; h.value = 'general';
            tc.appendChild(h);
        }
        try { dt.items.add(f.file); } catch (e) {}
    });
    if (fi) { try { fi.files = dt.files; } catch (e) {} }
    if (cnt) cnt.textContent = listaFotosEvidencia.length + ' foto(s)';
}
document.addEventListener('DOMContentLoaded', function () {
    initSignaturePadCreate();
    renderFotos();
    const fi = document.getElementById('inputFotosEvidencia');
    if (fi) fi.addEventListener('change', function () {
        for (const file of Array.from(this.files || [])) {
            if (listaFotosEvidencia.length >= 6) { alert('Máximo 6 fotos.'); break; }
            listaFotosEvidencia.push({ id: Date.now() + Math.random(), file: file });
        }
        renderFotos();
        this.value = '';
    });
    const p = document.getElementById('presupuestoInput');
    const a = document.getElementById('abonoInput');
    const t = document.getElementById('totalPreview');
    function upd() {
        const pv = parseFloat(p?.value || 0) || 0;
        const av = parseFloat(a?.value || 0) || 0;
        if (t) t.textContent = '$ ' + Math.max(0, pv - av).toLocaleString('es-CL');
    }
    if (p) p.addEventListener('input', upd);
    if (a) a.addEventListener('input', upd);
    upd();
    const g = document.getElementById('garantiaSelect');
    const gh = document.getElementById('garantiaHidden');
    if (g && gh) g.addEventListener('change', function () { gh.value = this.value; });
    const ts = document.getElementById('tipoCodigoSelect');
    const pw = document.getElementById('pinWrapper');
    if (ts && pw) ts.addEventListener('change', function () {
        pw.style.display = (this.value === 'pin') ? 'block' : 'none';
    });
    const cs = document.getElementById('clienteSearch');
    const ci = document.getElementById('clienteId');
    const cr = document.getElementById('clienteResultados');
    const cx = document.getElementById('clienteSeleccionadoInfo');
    let tm = null;
    if (cs) cs.addEventListener('input', function () {
        const q = this.value.trim();
        if (ci) ci.value = '';
        if (cx) cx.textContent = '';
        if (tm) clearTimeout(tm);
        if (q.length < 2) { if (cr) cr.innerHTML = ''; return; }
        tm = setTimeout(async function () {
            const resp = await fetch("{{ route('api.clientes.buscar') }}?q=" + encodeURIComponent(q));
            const datos = await resp.json();
            if (!cr) return;
            cr.innerHTML = '';
            (datos || []).slice(0, 8).forEach(function (c) {
                const nombre = ((c.nombre || '') + ' ' + (c.apellido || '')).trim();
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'list-group-item list-group-item-action';
                b.style.fontSize = '13px';
                b.textContent = nombre + ' - ' + (c.telefono || '');
                b.addEventListener('click', function () {
                    cs.value = nombre;
                    if (ci) ci.value = c.id;
                    if (cx) cx.textContent = 'OK ' + nombre;
                    cr.innerHTML = '';
                });
                cr.appendChild(b);
            });
        }, 300);
    });
    const form = document.getElementById('orderCreateForm');
    if (form) form.addEventListener('submit', function (e) {
        if (!ci || !ci.value) {
            e.preventDefault();
            alert('Selecciona un cliente.');
            return;
        }
        if (signaturePadCreate && !signaturePadCreate.isEmpty()) {
            document.getElementById('firmaRecepcionDataInput').value = signaturePadCreate.toDataURL('image/png');
        }
    });
    const crBtn = document.getElementById('crGuardar');
    if (crBtn) crBtn.addEventListener('click', async function () {
        const nombre = document.getElementById('crNombre')?.value.trim() || '';
        const apellido = document.getElementById('crApellido')?.value.trim() || '';
        const telefono = document.getElementById('crTelefono')?.value.trim() || '';
        const msg = document.getElementById('crMsg');
        if (!nombre || !apellido || !telefono) {
            if (msg) { msg.style.display = 'block'; msg.innerHTML = 'Nombre, apellido y teléfono obligatorios.'; }
            return;
        }
        crBtn.disabled = true;
        try {
            const token = document.querySelector('input[name="_token"]')?.value || '';
            const resp = await fetch("{{ route('reparaciones.cliente-rapido') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ nombre: nombre, apellido: apellido, telefono: telefono, dni: document.getElementById('crDni')?.value || null }),
            });
            const data = await resp.json();
            if (data.success && data.cliente) {
                if (cs) cs.value = data.cliente.nombre_completo;
                if (ci) ci.value = data.cliente.id;
                if (cx) cx.textContent = 'OK ' + data.cliente.nombre_completo;
                const modal = document.getElementById('modalClienteRapido');
                if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).hide();
            } else if (msg) {
                msg.style.display = 'block';
                msg.innerHTML = data.message || 'No se pudo guardar.';
            }
        } catch (e) {
            if (msg) { msg.style.display = 'block'; msg.innerHTML = 'Error de conexión.'; }
        } finally {
            crBtn.disabled = false;
        }
    });
});
</script>
@endpush

@endsection
