@extends('layouts.app')
@section('title', 'Actualizar Reparación')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.index') }}" style="color:#a855f7;">Reparaciones</a></li></ul>
    <ul><li class="breadcrumb-item"><a href="{{ route('reparaciones.show', $reparacion) }}" style="color:#a855f7;">{{ $reparacion->numero_orden }}</a></li></ul>
    <ul><li class="breadcrumb-item active">Editar</li></ul>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Actualizar Orden: {{ $reparacion->numero_orden }}</h5>
                        <p class="text-muted mb-0" style="font-size:13px;">
                            {{ $reparacion->dispositivo }} — {{ $reparacion->cliente->nombre_completo ?? '' }}
                        </p>
                    </div>
                    <a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i>Ver Detalle
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<ul><li style="font-size:13px;">{{ $e }}</li></ul>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('reparaciones.update', $reparacion) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="row g-4">
                        {{-- Estado y prioridad --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-tasks me-2" style="color:#a855f7;"></i>Estado de la Orden
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label for="estado" class="form-label">Estado Actual <span class="text-danger">*</span></label>
                                    <select name="estado" id="estado" class="form-select" required>
                                        @php $estados = ['recibido'=>'📥 Recibido','en_diagnostico'=>'🔍 En Diagnóstico','esperando_repuesto'=>'⏳ Esperando Repuesto','en_reparacion'=>'🔧 En Reparación','listo'=>'✅ Listo para Entregar','entregado'=>'📦 Entregado','no_reparable'=>'❌ No Reparable']; @endphp
                                        @foreach($estados as $val => $lbl)
                                            <option value="{{ $val }}" {{ old('estado',$reparacion->estado)==$val?'selected':'' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="prioridad" class="form-label">Prioridad</label>
                                    <select name="prioridad" id="prioridad" class="form-select">
                                        <option value="baja" {{ old('prioridad',$reparacion->prioridad)=='baja'?'selected':'' }}>🟢 Baja</option>
                                        <option value="media" {{ old('prioridad',$reparacion->prioridad)=='media'?'selected':'' }}>🟡 Media</option>
                                        <option value="alta" {{ old('prioridad',$reparacion->prioridad)=='alta'?'selected':'' }}>🟠 Alta</option>
                                        <option value="urgente" {{ old('prioridad',$reparacion->prioridad)=='urgente'?'selected':'' }}>🔴 Urgente</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="tecnico_id" class="form-label">Técnico Asignado</label>
                                    <select name="tecnico_id" id="tecnico_id" class="form-select">
                                        @foreach($tecnicos as $t)
                                            <option value="{{ $t->id }}" {{ old('tecnico_id',$reparacion->tecnico_id)==$t->id?'selected':'' }}>
                                                {{ $t->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Equipo --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-mobile-alt me-2" style="color:#a855f7;"></i>Equipo
                            </h6>
                            <div class="row g-3">
                                {{-- 1. Tipo de Dispositivo --}}
                                <div class="col-md-4">
                                    <label for="tipo_dispositivo" class="form-label">📱 Tipo de Dispositivo <span class="text-danger">*</span></label>
                                    <select name="tipo_dispositivo" id="tipo_dispositivo" class="form-select" required>
                                        <option value="">— Seleccionar tipo —</option>
                                        <option value="celular" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='celular'?'selected':'' }}>📱 Celular / Smartphone</option>
                                        <option value="tablet" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='tablet'?'selected':'' }}>📟 Tablet / iPad</option>
                                        <option value="portatil" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='portatil'?'selected':'' }}>💻 Portátil / Laptop</option>
                                        <option value="otros" {{ old('tipo_dispositivo',$reparacion->tipo_dispositivo)=='otros'?'selected':'' }}>🔧 Otros</option>
                                    </select>
                                </div>
                                {{-- 2. Marca --}}
                                <div class="col-md-4">
                                    <label for="marca_select" class="form-label">🏷️ Marca</label>
                                    @php
                                        $marcasPrecargadas = [
                                            'Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Motorola',
                                            'LG', 'Sony', 'Nokia', 'Alcatel', 'Honor',
                                            'Realme', 'Oppo', 'Vivo', 'OnePlus', 'Google',
                                            'HP', 'Dell', 'Lenovo', 'Acer', 'Asus',
                                            'Toshiba', 'Microsoft', 'HTC', 'ZTE', 'BlackBerry',
                                        ];
                                        $marcaActual = old('marca', $reparacion->marca ?? '');
                                        $esOtra = $marcaActual && !in_array($marcaActual, $marcasPrecargadas);
                                    @endphp
                                    <select name="marca_select" id="marca_select" class="form-select marca-select" onchange="toggleMarcaInputEdit(this)">
                                        <option value="">— Seleccionar o escribir —</option>
                                        @foreach($marcasPrecargadas as $m)
                                            <option value="{{ $m }}" {{ $marcaActual==$m?'selected':'' }}>{{ $m }}</option>
                                        @endforeach
                                        <option value="__otra__" {{ $esOtra?'selected':'' }}>✏️ Otra (escribir manualmente)</option>
                                    </select>
                                    <label for="marca" class="form-label mt-1">Otra marca</label>
                                    <input type="text" class="form-control marca-input" name="marca" id="marca"
                                           value="{{ old('marca',$reparacion->marca) }}"
                                           placeholder="Escribir marca manualmente..."
                                           style="{{ $esOtra ? 'display:block;' : 'display:none;' }}">
                                </div>
                                {{-- 3. Modelo --}}
                                <div class="col-md-4">
                                    <label for="modelo" class="form-label">📦 Modelo</label>
                                    <input type="text" class="form-control" name="modelo" id="modelo"
                                           value="{{ old('modelo',$reparacion->modelo) }}">
                                </div>
                                {{-- 4. IMEI --}}
                                <div class="col-md-4">
                                    <label for="imei" class="form-label">🔢 IMEI / Serie</label>
                                    <input type="text" class="form-control" name="imei" id="imei"
                                           value="{{ old('imei',$reparacion->imei) }}">
                                </div>
                                {{-- 5. Tipo (Patrón/PIN) --}}
                                <div class="col-md-4">
                                    <div class="d-flex gap-2 align-items-start">
                                        <div style="flex:0 0 100px;">
                                            <label for="tipo_codigo" class="form-label">🔐 Tipo</label>
                                            <select name="tipo_codigo" id="tipo_codigo" class="form-select" onchange="togglePatronInputEdit(this)">
                                                <option value="">—</option>
                                                <option value="pin" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='pin'?'selected':'' }}>🔢 PIN</option>
                                                <option value="patron" {{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'selected':'' }}>🔓 Patrón</option>
                                            </select>
                                        </div>
                                        <div style="flex:1;">
                                            <label for="codigo_equipo" class="form-label">Valor</label>
                                            <input type="text" class="form-control patron-valor" name="codigo_equipo" id="codigo_equipo"
                                                   value="{{ old('codigo_equipo',$reparacion->codigo_equipo) }}"
                                                   placeholder="PIN numérico"
                                                   style="display:{{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'none':'block'}};">
                                        </div>
                                    </div>
                                    {{-- Dibujo de patrón 3x3 --}}
                                    <div class="patron-dibujo mt-2" style="display:{{ old('tipo_codigo',$reparacion->tipo_codigo)=='patron'?'block':'none'}};">
                                        <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">Dibuja el patrón (toca los puntos en orden):</div>
                                        <div style="display:flex; gap:2px; flex-wrap:wrap; max-width:140px; margin:0 auto;">
                                            @for($i=1;$i<=9;$i++)
                                            <button type="button" class="patron-punto" data-pos="{{ $i }}"
                                                 style="width:40px; height:40px; border-radius:50%; border:2px solid #a855f7;
                                                        display:flex; align-items:center; justify-content:center;
                                                        font-size:13px; color:#a855f7; cursor:pointer; background:#f8f5ff;
                                                        transition:all .2s; user-select:none; padding:0;"
                                                 onclick="togglePuntoEdit(this)" aria-label="Punto {{ $i }} del patrón">
                                                {{ $i }}
                                            </button>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="patron_secuencia" class="patron-secuencia" value="{{ old('patron_secuencia') }}">
                                        <div style="display:flex; gap:4px; margin-top:4px;">
                                            <span style="font-size:11px; color:#9ca3af;" class="patron-texto">Ningún punto seleccionado</span>
                                            <button type="button" onclick="limpiarPatronEdit()" style="font-size:11px; border:none; background:transparent; color:#dc2626; cursor:pointer; padding:0;">✕ Limpiar</button>
                                        </div>
                                    </div>
                                </div>
                                {{-- 6. Color --}}
                                <div class="col-md-4">
                                    <label for="color" class="form-label">🎨 Color</label>
                                    <input type="text" class="form-control" name="color" id="color"
                                           value="{{ old('color',$reparacion->color) }}">
                                </div>
                                {{-- 7. Fecha Estimada --}}
                                <div class="col-md-4">
                                    <label for="fecha_estimada" class="form-label">📅 Fecha Estimada de Entrega</label>
                                    <input type="date" class="form-control" name="fecha_estimada" id="fecha_estimada"
                                           value="{{ old('fecha_estimada', optional($reparacion->fecha_estimada)->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Diagnóstico --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-stethoscope me-2" style="color:#a855f7;"></i>Diagnóstico Técnico
                            </h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="falla_reportada" class="form-label">Falla Reportada <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="falla_reportada" id="falla_reportada" rows="3" required>{{ old('falla_reportada',$reparacion->falla_reportada) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="diagnostico" class="form-label">Diagnóstico del Técnico</label>
                                    <textarea class="form-control" name="diagnostico" id="diagnostico" rows="4"
                                              placeholder="Describe el diagnóstico técnico del equipo...">{{ old('diagnostico',$reparacion->diagnostico) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="solucion" class="form-label">Solución Aplicada</label>
                                    <textarea class="form-control" name="solucion" id="solucion" rows="4"
                                              placeholder="Describe qué se hizo para solucionar la falla...">{{ old('solucion',$reparacion->solucion) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Costos y garantía --}}
                        <div class="col-12">
                            <h6 class="fw-600 mb-3" style="font-weight:600; color:#1e1b4b;">
                                <i class="fas fa-dollar-sign me-2" style="color:#a855f7;"></i>Costos y Garantía
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="presupuesto" class="form-label">Presupuesto (S/)</label>
                                    <input type="number" class="form-control" name="presupuesto" id="presupuesto"
                                           value="{{ old('presupuesto',$reparacion->presupuesto) }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label for="metodo_pago" class="form-label">Método de Pago (al entregar)</label>
                                    <select name="metodo_pago" id="metodo_pago" class="form-select">
                                        <option value="efectivo">💵 Efectivo</option>
                                        <option value="tarjeta">💳 Tarjeta</option>
                                        <option value="transferencia">🏦 Transferencia</option>
                                        <option value="yape">📱 Yape</option>
                                        <option value="plin">📲 Plin</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="costo_final" class="form-label">Costo Final (S/)</label>
                                    <input type="number" class="form-control" name="costo_final" id="costo_final"
                                           value="{{ old('costo_final',$reparacion->costo_final) }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label for="costo_repuesto" class="form-label">Costo de Repuesto(s) (S/)</label>
                                    <input type="number" class="form-control" name="costo_repuesto" id="costo_repuesto"
                                           value="{{ old('costo_repuesto',$reparacion->costo_repuesto) }}" min="0" step="0.01" placeholder="0.00">
                                    <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Opcional. Se resta para calcular la ganancia</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="comisionPorcentajeEdit" class="form-label">% Comision del Tecnico</label>
                                    <input type="number" class="form-control" name="comision_porcentaje" id="comisionPorcentajeEdit" value="@php echo old('comision_porcentaje', $reparacion->comision_porcentaje ?? $reparacion->tecnico->comision_porcentaje ?? ''); @endphp" min="0" max="100" step="0.01" placeholder="%">
                                    <div style="font-size:11px; color:#9ca3af; margin-top:2px;">Se pre-rellena con el % del perfil del tecnico</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="abono" class="form-label">Abono (S/)</label>
                                    <input type="number" class="form-control" name="abono" id="abono"
                                           value="{{ old('abono',$reparacion->abono) }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label for="total" class="form-label">Total (S/)</label>
                                    <input type="number" class="form-control total-auto" name="total" id="total"
                                           value="{{ old('total',$reparacion->total) }}" min="0" step="0.01" readonly
                                           style="background:#f3f4f6; font-weight:700;">
                                </div>
                                <div class="col-md-3">
                                    <label for="garantia" class="form-label">¿Incluye Garantía?</label>
                                    <select name="garantia" id="garantia" class="form-select">
                                        <option value="0" {{ !$reparacion->garantia?'selected':'' }}>No</option>
                                        <option value="1" {{ $reparacion->garantia?'selected':'' }}>Sí</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="dias_garantia" class="form-label">Días de Garantía</label>
                                    <input type="number" class="form-control" name="dias_garantia" id="dias_garantia"
                                           value="{{ old('dias_garantia',$reparacion->dias_garantia) }}" min="0">
                                </div>
                                <div class="col-12">
                                    <label for="cuponCodigoInput" class="form-label">🎟️ Cupón de Descuento</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="cupon_codigo" id="cuponCodigoInput"
                                               value="{{ old('cupon_codigo', session('cupon_codigo')) }}" placeholder="Ingresa el código del cupón (ej: CUP-XXXXXX-XXX)">
                                        <button type="button" class="btn btn-outline-primary" id="validarCuponBtn" onclick="validarCuponReparacion()">
                                            <i class="fas fa-check me-1"></i>Validar
                                        </button>
                                    </div>
                                    <div id="cuponInfo" class="mt-2" style="font-size:13px;"></div>
                                    <div style="font-size:11px; color:#9ca3af; margin-top:2px;">
                                        Si el cliente tiene un cupón de una venta anterior, ingrésalo aquí para aplicar el descuento en el repuesto.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-3" style="background:#fff7ed; border:2px solid #f59e0b;" id="comisionPreviewBox">
                                        <div style="font-weight:700; color:#9a3412; font-size:13px; margin-bottom:8px;">
                                            <i class="fas fa-coins me-1"></i>Vista Previa de Comision del Tecnico
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-2">
                                                <div style="font-size:11px; color:#6b7280;">Monto a cobrar</div>
                                                <div id="comisionPreviewCobrado" style="font-weight:600;">S/ 0.00</div>
                                            </div>
                                            <div class="col-md-2">
                                                <div style="font-size:11px; color:#6b7280;">Repuesto(s)</div>
                                                <div id="comisionPreviewRepuesto" style="color:#dc2626;">- S/ 0.00</div>
                                            </div>
                                            <div class="col-md-2">
                                                <div style="font-size:11px; color:#6b7280;">Base comision</div>
                                                <div id="comisionPreviewBase" style="color:#9a3412; font-weight:600;">S/ 0.00</div>
                                            </div>
                                            <div class="col-md-2">
                                                <div style="font-size:11px; color:#6b7280;">% del tecnico</div>
                                                <div id="comisionPreviewPct">0%</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div style="font-size:11px; color:#9a3412; font-weight:700;">Comision del tecnico</div>
                                                <div id="comisionPreviewMonto" style="color:#f59e0b; font-size:18px; font-weight:700;">S/ 0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <div class="col-12">
                                    <label for="notas" class="form-label">Notas adicionales</label>
                                    <textarea class="form-control" name="notas" id="notas" rows="2">{{ old('notas',$reparacion->notas) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-4">
                    @php
                        use App\Helpers\WhatsAppHelper;
                        $cliente = $reparacion->cliente;
                        $telefonoCliente = WhatsAppHelper::limpiarNumero($cliente->telefono ?? $cliente->celular);
                        $urlRecibido = WhatsAppHelper::generarUrl(
                            $telefonoCliente,
                            WhatsAppHelper::mensajeRecibido($reparacion, $empresa->nombre_tienda ?? 'CRM Celulares')
                        );
                        $urlListo = WhatsAppHelper::generarUrl(
                            $telefonoCliente,
                            WhatsAppHelper::mensajeListo($reparacion, $empresa->nombre_tienda ?? 'CRM Celulares')
                        );
                    @endphp
                    @if($urlRecibido)
                    <div class="mb-3">
                        <div class="form-label fw-600" style="font-size:13px; color:#1e1b4b;">
                            <i class="fab fa-whatsapp me-1" style="color:#25D366;"></i>Notificar al Cliente por WhatsApp
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ $urlRecibido }}" target="_blank"
                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                <i class="fab fa-whatsapp me-1"></i>📩 Notificar Recibido
                            </a>
                            <a href="{{ $urlListo }}" target="_blank"
                               class="btn btn-sm" style="background:#25D366; color:#fff; border-radius:8px;">
                                <i class="fab fa-whatsapp me-1"></i>📩 Notificar Listo/Entregado
                            </a>
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-top:4px;">
                            📞 {{ $reparacion->cliente->telefono ?? '—' }}
                        </div>
                    </div>
                    @endif

                    {{-- ✍️ FIRMA DE ENTREGA (solo si se marca como entregado) --}}
                    <div class="mb-3" id="firmaEntregaSection" style="display:none;">
                        <div class="form-label fw-600" style="font-size:13px; color:#1e1b4b;">
                            <i class="fas fa-pen me-1" style="color:#a855f7;"></i>Firma de Entrega
                        </div>
                        <p class="text-muted" style="font-size:12px;">Haz que el cliente firme al entregar el equipo.</p>
                        @if($reparacion->firma_entrega)
                            <div class="text-center mb-2" id="firmaEntregaExistente">
                                <img src="{{ asset('storage/'.$reparacion->firma_entrega) }}" alt="Firma de entrega"
                                     style="max-width:100%; max-height:100px; border:1px solid #e5e7eb; border-radius:8px; background:#fff;">
                                <p class="text-muted mt-1" style="font-size:11px;">✓ Firma de entrega ya registrada</p>
                            </div>
                        @endif
                        <div class="signature-pad-wrapper" id="sigPadEntregaWrapperEdit" style="border:2px dashed #d1d5db; border-radius:12px; background:#fff; position:relative; cursor:crosshair;">
                            <canvas id="sigCanvasEntregaEdit" style="display:block; width:100%; height:160px; border-radius:12px; touch-action:none;"></canvas>
                            <div class="placeholder" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:#9ca3af; font-size:14px; pointer-events:none;">Firma aquí</div>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarFirmaEdit()">
                                <i class="fas fa-eraser me-1"></i>Limpiar
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="guardarFirmaEdit()">
                                <i class="fas fa-check me-1"></i>Guardar Firma
                            </button>
                        </div>
                        <input type="hidden" name="firma_entrega_data" id="firmaEntregaData" value="">
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('reparaciones.show', $reparacion) }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js" integrity="sha384-dPowQo9uxJU703klzvnG+vzLHQDNmO/zREXw6BhCMupB54CE70wj6SWOGVPySK3s" crossorigin="anonymous"></script>
<script>
// ── FIRMA DE ENTREGA ──
let sigPadEntregaEdit = null;

function initFirmaEntregaEdit() {
    const canvas = document.getElementById('sigCanvasEntregaEdit');
    if (!canvas) return;
    const wrapper = document.getElementById('sigPadEntregaWrapperEdit');
    canvas.width = wrapper.clientWidth || 300;
    canvas.height = 160;
    sigPadEntregaEdit = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
    sigPadEntregaEdit.addEventListener('beginStroke', () => {
        const placeholder = wrapper.querySelector('.placeholder');
        if (placeholder) placeholder.style.display = 'none';
    });
}

function mostrarFirmaEntrega() {
    const section = document.getElementById('firmaEntregaSection');
    if (section) section.style.display = 'block';
    setTimeout(() => initFirmaEntregaEdit(), 100);
}

function ocultarFirmaEntrega() {
    const section = document.getElementById('firmaEntregaSection');
    if (section) section.style.display = 'none';
}

function limpiarFirmaEdit() {
    if (sigPadEntregaEdit) {
        sigPadEntregaEdit.clear();
        const wrapper = document.getElementById('sigPadEntregaWrapperEdit');
        const placeholder = wrapper?.querySelector('.placeholder');
        if (placeholder) placeholder.style.display = '';
    }
    document.getElementById('firmaEntregaData').value = '';
}

function guardarFirmaEdit() {
    if (!sigPadEntregaEdit) return;
    if (sigPadEntregaEdit.isEmpty()) {
        alert('Por favor, dibuja la firma antes de guardar.');
        return;
    }
    const dataUrl = sigPadEntregaEdit.toDataURL('image/png');
    document.getElementById('firmaEntregaData').value = dataUrl;
    // Ocultar el pad y mostrar mensaje de éxito
    const wrapper = document.getElementById('sigPadEntregaWrapperEdit');
    const existing = document.getElementById('firmaEntregaExistente');
    if (existing) {
        existing.querySelector('img').src = dataUrl;
        existing.querySelector('p').textContent = '✓ Nueva firma registrada';
    }
    wrapper.style.display = 'none';
    alert('Firma capturada correctamente. Guarda los cambios para confirmar.');
}

// Mostrar/ocultar firma según el estado seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.querySelector('select[name="estado"]');
    if (estadoSelect) {
        estadoSelect.addEventListener('change', function() {
            if (this.value === 'entregado') {
                mostrarFirmaEntrega();
            } else {
                ocultarFirmaEntrega();
            }
        });
        // Si ya está en entregado, mostrar inmediatamente
        if (estadoSelect.value === 'entregado') {
            mostrarFirmaEntrega();
        }
    }
});

// ── Toggle Marca (precargada / otra) ──
function toggleMarcaInputEdit(select) {
    const input = select.closest('.col-md-4').querySelector('.marca-input');
    const marcaSelect = select.value;
    if (marcaSelect === '__otra__') {
        input.style.display = 'block';
        input.value = '';
        input.focus();
    } else {
        input.style.display = 'none';
        input.value = marcaSelect;
    }
}

// ── Dibujo de Patrón 3x3 ──
let patronPuntosEdit = [];

function togglePatronInputEdit(select) {
    const container = select.closest('.col-md-4');
    const dibujo = container.querySelector('.patron-dibujo');
    const valorInput = container.querySelector('.patron-valor');
    const tipo = select.value;

    if (tipo === 'patron') {
        dibujo.style.display = 'block';
        valorInput.style.display = 'none';
        valorInput.value = '';
    } else if (tipo === 'pin') {
        dibujo.style.display = 'none';
        valorInput.style.display = 'block';
        valorInput.placeholder = 'PIN numérico (ej: 1234)';
        limpiarPatronEdit();
    } else {
        dibujo.style.display = 'none';
        valorInput.style.display = 'block';
        valorInput.placeholder = 'Valor del PIN o patrón';
        limpiarPatronEdit();
    }
}

function togglePuntoEdit(el) {
    const container = el.closest('.col-md-4');
    const pos = parseInt(el.dataset.pos);
    const idx = patronPuntosEdit.indexOf(pos);

    if (idx === -1) {
        patronPuntosEdit.push(pos);
        el.style.background = 'linear-gradient(135deg, #a855f7, #ec4899)';
        el.style.color = '#fff';
        el.style.borderColor = 'transparent';
        el.style.transform = 'scale(1.1)';
        el.textContent = patronPuntosEdit.length;
    } else {
        patronPuntosEdit.splice(idx, 1);
        el.style.background = '#f8f5ff';
        el.style.color = '#a855f7';
        el.style.borderColor = '#a855f7';
        el.style.transform = 'scale(1)';
        patronPuntosEdit.forEach((p, i) => {
            const punto = container.querySelector(`.patron-punto[data-pos="${p}"]`);
            if (punto) punto.textContent = i + 1;
        });
    }

    actualizarPatronTextoEdit(container);
}

function limpiarPatronEdit() {
    patronPuntosEdit = [];
    document.querySelectorAll('.patron-punto').forEach(el => {
        el.style.background = '#f8f5ff';
        el.style.color = '#a855f7';
        el.style.borderColor = '#a855f7';
        el.style.transform = 'scale(1)';
        el.textContent = el.dataset.pos;
    });
    document.querySelectorAll('.patron-texto').forEach(el => el.textContent = 'Ningún punto seleccionado');
    document.querySelectorAll('.patron-secuencia').forEach(el => el.value = '');
}

function actualizarPatronTextoEdit(container) {
    const texto = container.querySelector('.patron-texto');
    const hidden = container.querySelector('.patron-secuencia');
    if (patronPuntosEdit.length === 0) {
        texto.textContent = 'Ningún punto seleccionado';
        hidden.value = '';
    } else {
        const secuencia = patronPuntosEdit.join('-');
        texto.textContent = `Secuencia: ${secuencia}`;
        hidden.value = secuencia;
    }
}

// ── Auto-calcular Total = Costo Final / Presupuesto - Abono ──
document.addEventListener('input', function(e) {
    if (['presupuesto', 'abono', 'costo_final'].includes(e.target.name)) {
        const presupuesto = parseFloat(document.querySelector('input[name="presupuesto"]').value) || 0;
        const costoFinal = parseFloat(document.querySelector('input[name="costo_final"]')?.value) || 0;
        const abono = parseFloat(document.querySelector('input[name="abono"]').value) || 0;
        const totalInput = document.querySelector('input[name="total"]');
        const base = costoFinal > 0 ? costoFinal : presupuesto;
        if (totalInput) totalInput.value = Math.max(0, base - abono).toFixed(2);
    }
});

// ── VALIDAR CUPÓN DE DESCUENTO ──
function validarCuponReparacion() {
    const codigo = document.getElementById('cuponCodigoInput').value.trim();
    const infoDiv = document.getElementById('cuponInfo');

    if (!codigo) {
        infoDiv.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Ingresa un código de cupón.</span>';
        return;
    }

    fetch('{{ route("api.cupon.validar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ codigo: codigo }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const cupon = data.cupon;
            const valorTexto = cupon.tipo === 'porcentaje' ? cupon.valor + '%' : 'S/ ' + cupon.valor;
            infoDiv.innerHTML = `
                <div class="alert alert-success py-2 px-3 mb-0" style="font-size:13px;">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>Cupón válido:</strong> ${valorTexto} de descuento
                    ${cupon.descripcion ? '<br><small class="text-muted">' + cupon.descripcion + '</small>' : ''}
                </div>
            `;
            document.getElementById('validarCuponBtn').classList.add('btn-success');
            document.getElementById('validarCuponBtn').classList.remove('btn-outline-primary');
        } else {
            infoDiv.innerHTML = `
                <div class="alert alert-danger py-2 px-3 mb-0" style="font-size:13px;">
                    <i class="fas fa-times-circle me-1"></i>${data.message || 'Cupón no válido.'}
                </div>
            `;
            document.getElementById('validarCuponBtn').classList.remove('btn-success');
            document.getElementById('validarCuponBtn').classList.add('btn-outline-primary');
        }
    })
    .catch(err => {
        infoDiv.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0" style="font-size:13px;"><i class="fas fa-times-circle me-1"></i>Error de conexión al validar el cupón.</div>';
        console.error(err);
    });
}
</script>
@endpush