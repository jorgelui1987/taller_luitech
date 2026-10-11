@extends('layouts.app')
@section('title', 'Actualizar Reparación')
@section('breadcrumb')
<ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}">Reparaciones</a></li></ul>
<ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.show', $reparacion) }}">{{ $reparacion->numero_orden }}</a></li></ul>
<ul><li class="breadcrumb-item active">Editar</li></ul>
@endsection
@push('styles')
<style>
.st-block{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.04);}
.st-head{border-bottom:1px solid #f1f5f9;padding-bottom:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.st-dot{width:12px;height:12px;border-radius:50%;flex:none;}
.st-head h2{font-size:16px;font-weight:700;color:#0f172a;margin:0;}
.st-label{font-size:13px;font-weight:600;color:#334155;margin-bottom:4px;display:block;}
.st-input{width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:9px 12px;background:#f8fafc;font-size:14px;color:#0f172a;}
.st-input:focus{background:#fff;border-color:var(--accent1);outline:none;}
.signature-pad-wrapper{border:2px dashed #d1d5db;border-radius:12px;background:#fff;position:relative;cursor:crosshair;}
.signature-pad-wrapper canvas{display:block;width:100%;height:140px;touch-action:none;}
.top-actions{display:flex;gap:8px;flex-wrap:wrap;}
</style>
@endpush
@section('content')
<div class="container-fluid px-1">
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
<div><h5 class="fw-bold mb-1">Actualizar Orden: {{ $reparacion->numero_orden }}</h5>
<p class="text-muted mb-0" style="font-size:13px;">{{ $reparacion->dispositivo }} — {{ $reparacion->cliente->nombre_completo ?? '' }}</p></div>
<div class="top-actions">
<a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary btn-sm">Ver Detalle</a>
<button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm">Imprimir Orden</button>
</div></div>
@if($errors->any())
<div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('reparaciones.update', $reparacion) }}" method="POST" id="editForm" oninput="stCalcular()">
@csrf @method('PUT')
<div class="st-block mb-3">
<div class="st-head"><span class="st-dot" style="background:#0284c7;"></span><h2>1. Estado y Asignación</h2></div>
<div class="row g-3">
<div class="col-md-4"><label class="st-label">Estado *</label>
<select name="estado" id="estado" class="st-input" required>
@php $estados = ['recibido'=>'Recibido','en_diagnostico'=>'En Diagnóstico','esperando_repuesto'=>'Esperando Repuesto','en_reparacion'=>'En Reparación','listo'=>'Listo para Entregar','entregado'=>'Entregado','no_reparable'=>'No Reparable']; @endphp
@foreach($estados as $val => $lbl)<option value="{{ $val }}" {{ old('estado',$reparacion->estado)==$val?'selected':'' }}>{{ $lbl }}</option>@endforeach
</select></div>
<div class="col-md-4"><label class="st-label">Prioridad</label>
<select name="prioridad" id="prioridad" class="st-input">
<option value="baja" {{ old('prioridad',$reparacion->prioridad)=='baja'?'selected':'' }}>Baja</option>
<option value="media" {{ old('prioridad',$reparacion->prioridad)=='media'?'selected':'' }}>Media</option>
<option value="alta" {{ old('prioridad',$reparacion->prioridad)=='alta'?'selected':'' }}>Alta</option>
<option value="urgente" {{ old('prioridad',$reparacion->prioridad)=='urgente'?'selected':'' }}>Urgente</option>
</select></div>
<div class="col-md-4"><label class="st-label">Técnico Asignado</label>
<select name="tecnico_id" id="tecnico_id" class="st-input">
@foreach($tecnicos as $t)<option value="{{ $t->id }}" {{ old('tecnico_id',$reparacion->tecnico_id)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
</select></div>
</div></div>

<div class="st-block mb-3">
<div class="st-head"><span class="st-dot" style="background:#6366f1;"></span><h2>2. Datos del Equipo</h2></div>
<div class="row g-3">
<div class="col-md-3"><label class="st-label">Tipo de Equipo *</label>
<select name="tipo_dispositivo" class="st-input" required>
<option value="">— Seleccionar tipo —</option>
<option value="celular" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='celular'?'selected':'' }}>Celular / Smartphone</option>
<option value="tablet" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='tablet'?'selected':'' }}>Tablet / iPad</option>
<option value="portatil" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='portatil'?'selected':'' }}>Portátil / Laptop</option>
<option value="otros" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='otros'?'selected':'' }}>Otros</option>
</select></div>
<div class="col-md-3"><label class="st-label">Marca</label>
<input type="text" name="marca" class="st-input" value="{{ old('marca',$reparacion->marca) }}" placeholder="Ej: Xiaomi"></div>
<div class="col-md-3"><label class="st-label">Modelo</label>
<input type="text" name="modelo" class="st-input" value="{{ old('modelo',$reparacion->modelo) }}" placeholder="Ej: 12"></div>
<div class="col-md-3"><label class="st-label">IMEI / Serie</label>
<input type="text" name="imei" class="st-input" value="{{ old('imei',$reparacion->imei) }}" placeholder="15 dígitos"></div>
<div class="col-md-3"><label class="st-label">Color</label>
<input type="text" name="color" class="st-input" value="{{ old('color',$reparacion->color) }}"></div>
<div class="col-md-3"><label class="st-label">Fecha Estimada</label>
<input type="date" name="fecha_estimada" class="st-input" value="{{ old('fecha_estimada', optional($reparacion->fecha_estimada)->format('Y-m-d')) }}"></div>
<div class="col-md-3"><label class="st-label">Bloqueo</label>
<select name="tipo_codigo" class="st-input">
<option value="">—</option>
<option value="pin" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='pin'?'selected':'' }}>PIN</option>
<option value="patron" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'selected':'' }}>Patrón</option>
</select></div>
<div class="col-md-3"><label class="st-label">Clave / Patrón</label>
<input type="text" name="codigo_equipo" class="st-input" value="{{ old('codigo_equipo',$reparacion->codigo_equipo) }}"></div>
</div></div>
<div class="st-block mb-3">
<div class="st-head"><span class="st-dot" style="background:#f59e0b;"></span><h2>3. Diagnóstico y Solución</h2></div>
<div class="row g-3">
<div class="col-md-6"><label class="st-label">Falla Reportada *</label>
<textarea name="falla_reportada" rows="3" required class="st-input">{{ old('falla_reportada',$reparacion->falla_reportada) }}</textarea></div>
<div class="col-md-6"><label class="st-label">Diagnóstico del Técnico</label>
<textarea name="diagnostico" rows="3" class="st-input">{{ old('diagnostico',$reparacion->diagnostico) }}</textarea></div>
<div class="col-md-6"><label class="st-label">Solución Aplicada</label>
<textarea name="solucion" rows="2" class="st-input">{{ old('solucion',$reparacion->solucion) }}</textarea></div>
<div class="col-md-6"><label class="st-label">Notas Adicionales</label>
<textarea name="notas" rows="2" class="st-input">{{ old('notas',$reparacion->notas) }}</textarea></div>
</div></div>
<div class="st-block mb-3">
<div class="st-head"><span class="st-dot" style="background:#10b981;"></span><h2>4. Valores, Garantía y Pago</h2></div>
<div class="row g-3">
<div class="col-md-3"><label class="st-label">Presupuesto ({{ $empresa->simbolo_moneda ?? '$' }})</label>
<input type="number" name="presupuesto" id="presupuesto" class="st-input fw-bold" value="{{ old('presupuesto',$reparacion->presupuesto) }}" min="0" step="0.01"></div>
<div class="col-md-3"><label class="st-label">Método de Pago</label>
<select name="metodo_pago" class="st-input">
<option value="efectivo" {{ old('metodo_pago',$reparacion->metodo_pago ?? 'efectivo')=='efectivo'?'selected':'' }}>Efectivo</option>
<option value="transferencia" {{ old('metodo_pago',$reparacion->metodo_pago ?? '')=='transferencia'?'selected':'' }}>Transferencia</option>
<option value="mercadopago" {{ old('metodo_pago',$reparacion->metodo_pago ?? '')=='mercadopago'?'selected':'' }}>Mercado Pago</option>
</select></div>
<div class="col-md-3"><label class="st-label">Costo Final ({{ $empresa->simbolo_moneda ?? '$' }})</label>
<input type="number" name="costo_final" id="costo_final" class="st-input fw-bold" value="{{ old('costo_final',$reparacion->costo_final) }}" min="0" step="0.01"></div>
<div class="col-md-3"><label class="st-label">Costo Repuesto(s) ({{ $empresa->simbolo_moneda ?? '$' }})</label>
<input type="number" name="costo_repuesto" id="costo_repuesto" class="st-input" value="{{ old('costo_repuesto',$reparacion->costo_repuesto) }}" min="0" step="0.01"></div>
<div class="col-md-3"><label class="st-label">% Comisión Técnico</label>
<input type="number" name="comision_porcentaje" id="pctComision" class="st-input" value="{{ old('comision_porcentaje', $reparacion->comision_porcentaje ?? '') }}" min="0" max="100" step="0.01"></div>
<div class="col-md-3"><label class="st-label">Abono ({{ $empresa->simbolo_moneda ?? '$' }})</label>
<input type="number" name="abono" id="abono" class="st-input" value="{{ old('abono',$reparacion->abono) }}" min="0" step="0.01"></div>
<div class="col-md-3"><label class="st-label">Saldo Pendiente ({{ $empresa->simbolo_moneda ?? '$' }})</label>
<input type="number" name="total" id="total" class="st-input fw-bold" value="{{ old('total',$reparacion->total) }}" readonly style="background:#f1f5f9;"></div>
<div class="col-md-3"><label class="st-label">Garantía</label>
<div class="d-flex gap-2">
<select name="garantia" class="st-input">
<option value="0" {{ !$reparacion->garantia?'selected':'' }}>No</option>
<option value="1" {{ $reparacion->garantia?'selected':'' }}>Sí</option>
</select>
<input type="number" name="dias_garantia" class="st-input" value="{{ old('dias_garantia',$reparacion->dias_garantia ?? 30) }}" min="0" placeholder="Días">
</div></div>
</div>
<div class="mt-3 p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#f0f9ff;border:1px solid #bae6fd;">
<span style="font-size:13px;font-weight:600;color:#075985;">Cálculo Automático de Comisión:</span>
<b id="resumenComision" style="font-size:15px;color:#0c4a6e;">Comisión: $ 0.00</b>
</div></div>

<div class="st-block mb-3">
<div class="st-head"><span class="st-dot" style="background:#14b8a6;"></span><h2>5. Contacto, Firma y Guardado</h2></div>
<div class="row g-3">
<div class="col-md-4">
<label class="st-label">WhatsApp Cliente</label>
<div class="form-text mb-2" style="font-size:12px;">{{ $reparacion->cliente->telefono ?? '—' }}</div>
@php
$telCli = \App\Helpers\WhatsAppHelper::limpiarNumero($reparacion->cliente->telefono ?? $reparacion->cliente->celular ?? '');
$urlRec = $telCli ? \App\Helpers\WhatsAppHelper::generarUrl($telCli, \App\Helpers\WhatsAppHelper::mensajeRecibido($reparacion, $empresa->nombre_tienda ?? 'Taller')) : null;
$urlOk = $telCli ? \App\Helpers\WhatsAppHelper::generarUrl($telCli, \App\Helpers\WhatsAppHelper::mensajeListo($reparacion, $empresa->nombre_tienda ?? 'Taller')) : null;
@endphp
@if($urlRec)
<div class="d-flex gap-2 flex-wrap">
<a href="{{ $urlRec }}" target="_blank" class="btn btn-sm text-white" style="background:#25D366;">Recibido</a>
<a href="{{ $urlOk }}" target="_blank" class="btn btn-sm text-white" style="background:#25D366;">Listo</a>
</div>
@endif
</div>
<div class="col-md-4">
<label class="st-label">Firma de Entrega</label>
@if($reparacion->firma_entrega)
<img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma" style="max-width:100%;max-height:80px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;">
@endif
<div class="signature-pad-wrapper mt-2" id="sigPadEntregaWrapperEdit">
<canvas id="sigCanvasEntregaEdit"></canvas>
<div class="placeholder">Firma aquí</div>
</div>
<div class="d-flex gap-2 mt-2">
<button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarFirmaEdit()">Limpiar</button>
<button type="button" class="btn btn-sm btn-primary" onclick="guardarFirmaEdit()">Guardar Firma</button>
</div>
<input type="hidden" name="firma_entrega_data" id="firmaEntregaData" value="">
</div>
<div class="col-md-4">
<label class="st-label">Resumen</label>
<div class="p-2 rounded-3 mb-3" style="background:#f8fafc;font-size:13px;line-height:2;">
<div><b>{{ $reparacion->numero_orden }}</b> · {{ $reparacion->dispositivo }}</div>
<div>{{ $reparacion->cliente->nombre_completo ?? '' }}</div>
<div>Total: <b>{{ ($empresa->simbolo_moneda ?? '$') . ' ' . number_format($reparacion->total ?? 0, 0) }}</b></div>
</div>
<div class="d-grid gap-2">
<button type="submit" class="btn ui-btn-brand btn-lg">Guardar Cambios</button>
<a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
</div>
</div>
</div>
</form>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
function stCalcular() {
    const cf = parseFloat(document.getElementById('costo_final')?.value || 0) || 0;
    const cr = parseFloat(document.getElementById('costo_repuesto')?.value || 0) || 0;
    const pc = parseFloat(document.getElementById('pctComision')?.value || 0) || 0;
    const ab = parseFloat(document.getElementById('abono')?.value || 0) || 0;
    const base = Math.max(0, (cf > 0 ? cf : parseFloat(document.getElementById('presupuesto')?.value || 0) || 0) - cr);
    const com = base * (pc / 100);
    const el = document.getElementById('resumenComision');
    if (el) el.textContent = 'Comisión: $ ' + com.toLocaleString('es-CL', {minimumFractionDigits: 2}) + ' (' + pc + '% de $ ' + base.toLocaleString('es-CL') + ')';
    const t = document.getElementById('total');
    const p = parseFloat(document.getElementById('presupuesto')?.value || 0) || 0;
    if (t && document.activeElement !== t) t.value = Math.max(0, (cf > 0 ? cf : p) - ab).toFixed(0);
}
let sigPadEntregaEdit = null;
function initFirmaEntregaEdit() {
    const canvas = document.getElementById('sigCanvasEntregaEdit');
    if (!canvas || typeof SignaturePad === 'undefined') return;
    const r = Math.max(window.devicePixelRatio || 1, 1);
    const w = canvas.parentElement.clientWidth || 300;
    canvas.width = w * r; canvas.height = 140 * r;
    canvas.getContext('2d').setTransform(r, 0, 0, r, 0, 0);
    sigPadEntregaEdit = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
}
function limpiarFirmaEdit() {
    if (sigPadEntregaEdit) sigPadEntregaEdit.clear();
    document.getElementById('firmaEntregaData').value = '';
}
function guardarFirmaEdit() {
    if (!sigPadEntregaEdit || sigPadEntregaEdit.isEmpty()) { alert('Dibuja la firma primero.'); return; }
    document.getElementById('firmaEntregaData').value = sigPadEntregaEdit.toDataURL('image/png');
    alert('Firma capturada. Guarda los cambios para confirmar.');
}
function toggleMarcaInputEdit() {}
document.addEventListener('DOMContentLoaded', function () {
    initFirmaEntregaEdit();
    stCalcular();
    const est = document.getElementById('estado');
    if (est) est.addEventListener('change', function () {
        const sec = document.getElementById('firmaEntregaSection');
    });
});
</script>
@endpush

