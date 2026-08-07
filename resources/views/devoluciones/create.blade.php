@extends('layouts.app')
@section('title', 'Nueva Devolución')

@section('breadcrumb')
    <ul><li class="breadcrumb-item"><a href="{{ route('devoluciones.index') }}">Devoluciones</a></li></ul>
    <ul><li class="breadcrumb-item active">Nueva Devolución</li></ul>
@endsection

@section('content')

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <ul><li>{{ $error }}</li></ul>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- ── Header ── -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1" style="color:#1e1b4b;"><i class="fas fa-undo-alt me-2" style="color:#a855f7;"></i>Registrar Devolución</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Selecciona la venta y los productos que el cliente devuelve</p>
    </div>
    <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<form id="formDevolucion" method="POST" action="{{ route('devoluciones.store') }}">
    @csrf

    <div class="row g-4">
        <!-- ════════ COLUMNA IZQUIERDA: Datos de la devolución ════════ -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-shopping-cart me-2" style="color:#a855f7;"></i>Venta Original</h6>

                    <div class="mb-3">
                        <label for="ventaSelect" class="form-label">Seleccionar venta <span class="text-danger">*</span></label>
                        <select name="venta_id" id="ventaSelect" class="form-select @error('venta_id') is-invalid @enderror" required>
                            <option value="">Seleccionar venta...</option>
                            @foreach($ventas as $v)
                                <option value="{{ $v->id }}" {{ old('venta_id', $ventaSeleccionada->id ?? '') == $v->id ? 'selected' : '' }}>
                                    {{ $v->numero_venta }} - {{ $v->fecha_venta->format('d/m/Y') }} - {{ $v->cliente->nombre_completo ?? 'Venta general' }}
                                </option>
                            @endforeach
                        </select>
                        @error('venta_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="ventaInfo" class="mb-3 p-3 rounded-3" style="background:#f8f5ff;display:none;">
                        <div style="font-size:12px;color:#7c3aed;font-weight:600;">Datos de la venta</div>
                        <div id="ventaInfoContenido" style="font-size:12px;margin-top:6px;"></div>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3"><i class="fas fa-tag me-2" style="color:#a855f7;"></i>Información de la Devolución</h6>

                    <div class="mb-3">
                        <span class="form-label d-block">Tipo de devolución <span class="text-danger">*</span></span>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipoDevolucion" value="devolucion"
                                       {{ old('tipo', 'devolucion') == 'devolucion' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="tipoDevolucion">
                                    <i class="fas fa-undo-alt me-1" style="color:#06b6d4;"></i> Devolución
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipoGarantia" value="garantia"
                                       {{ old('tipo') == 'garantia' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="tipoGarantia">
                                    <i class="fas fa-shield-alt me-1" style="color:#10b981;"></i> Garantía
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo <span class="text-danger">*</span></label>
                        <select name="motivo" id="motivo" class="form-select @error('motivo') is-invalid @enderror" required>
                            <option value="">Seleccionar motivo...</option>
                            <option value="garantia" {{ old('motivo')=='garantia'?'selected':'' }}>🛡️ Garantía</option>
                            <option value="defecto" {{ old('motivo')=='defecto'?'selected':'' }}>⚠️ Producto defectuoso</option>
                            <option value="cambio_opinion" {{ old('motivo')=='cambio_opinion'?'selected':'' }}>💭 Cambio de opinión</option>
                            <option value="error_venta" {{ old('motivo')=='error_venta'?'selected':'' }}>❌ Error de venta</option>
                            <option value="otro" {{ old('motivo')=='otro'?'selected':'' }}>📦 Otro</option>
                        </select>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tipo_reembolso" class="form-label">Tipo de reembolso</label>
                        <select name="tipo_reembolso" id="tipo_reembolso" class="form-select">
                            <option value="">Sin reembolso</option>
                            <option value="efectivo" {{ old('tipo_reembolso')=='efectivo'?'selected':'' }}>💵 Efectivo</option>
                            <option value="tarjeta" {{ old('tipo_reembolso')=='tarjeta'?'selected':'' }}>💳 Tarjeta</option>
                            <option value="transferencia" {{ old('tipo_reembolso')=='transferencia'?'selected':'' }}>🏦 Transferencia</option>
                            <option value="nota_credito" {{ old('tipo_reembolso')=='nota_credito'?'selected':'' }}>📄 Nota de crédito</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea name="observacion" id="observacion" class="form-control" rows="3" maxlength="1000"
                                  placeholder="Ej: El cliente trae el equipo con falla en la pantalla...">{{ old('observacion') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════ COLUMNA DERECHA: Productos a devolver ════════ -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-box me-2" style="color:#a855f7;"></i>Productos a Devolver</h6>
                        <span class="badge" style="background:#f3e8ff;color:#7c3aed;font-size:11px;">Seleccione una venta primero</span>
                    </div>

                    <div id="sinVentaMsg" class="text-center py-5">
                        <i class="fas fa-shopping-cart" style="font-size:48px;color:#e5e7eb;"></i>
                        <p class="text-muted mt-3 mb-0">Selecciona una venta para cargar sus productos</p>
                    </div>

                    <div id="productosContainer" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="productosTable">
                                <thead>
                                    <tr>
                                        <th style="width:40px;"><label for="checkAll" class="visually-hidden">Seleccionar todos</label><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                        <th>Producto</th>
                                        <th class="text-center" style="width:90px;">Vendido</th>
                                        <th class="text-center" style="width:90px;">Devuelto ya</th>
                                        <th class="text-center" style="width:110px;">Cantidad a devolver</th>
                                        <th style="width:130px;">Condición</th>
                                    </tr>
                                </thead>
                                <tbody id="productosTbody"></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3 mb-3">
                            <div class="p-3 rounded-3 me-3" style="background:#f8f5ff;min-width:220px;">
                                <div class="d-flex justify-content-between" style="font-size:12px;">
                                    <span class="text-muted">Subtotal devolución</span>
                                    <strong id="resumenSubtotal">S/ 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between" style="font-size:12px;margin-top:4px;">
                                    <span class="text-muted">IGV</span>
                                    <strong id="resumenImpuesto">S/ 0.00</strong>
                                </div>
                                <hr style="margin:8px 0;">
                                <div class="d-flex justify-content-between">
                                    <span style="font-weight:600;">Total a devolver</span>
                                    <strong style="color:#7c3aed;font-size:16px;" id="resumenTotal">S/ 0.00</strong>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnSubmit" disabled>
                            <i class="fas fa-check-circle me-2"></i>Registrar Devolución
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ventaSelect = document.getElementById('ventaSelect');
    const ventaInfo = document.getElementById('ventaInfo');
    const ventaInfoContenido = document.getElementById('ventaInfoContenido');
    const sinVentaMsg = document.getElementById('sinVentaMsg');
    const productosContainer = document.getElementById('productosContainer');
    const productosTbody = document.getElementById('productosTbody');
    const checkAll = document.getElementById('checkAll');
    const btnSubmit = document.getElementById('btnSubmit');
    const igvPorcentaje = {{ \App\Models\Configuracion::empresa()->igv ?? 18 }};

    // Si hay una venta preseleccionada (por error de validación), cargar sus datos
    const ventaPreseleccionada = '{{ old('venta_id', $ventaSeleccionada->id ?? '') }}';
    if (ventaPreseleccionada) {
        cargarVenta(ventaPreseleccionada);
    }

    ventaSelect.addEventListener('change', function() {
        if (this.value) {
            cargarVenta(this.value);
        } else {
            ventaInfo.style.display = 'none';
            sinVentaMsg.style.display = 'block';
            productosContainer.style.display = 'none';
            productosTbody.innerHTML = '';
            checkAll.checked = false;
            checkAll.disabled = true;
            btnSubmit.disabled = true;
        }
    });

    function cargarVenta(ventaId) {
        fetch('{{ route('devoluciones.api.venta', ['ventaId' => '__ID__']) }}'.replace('__ID__', ventaId))
            .then(res => res.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }

                ventaInfo.style.display = 'block';
                ventaInfoContenido.innerHTML =
                    '<div class="d-flex justify-content-between"><span class="text-muted">N° Venta</span><strong>' + data.venta.numero_venta + '</strong></div>' +
                    '<div class="d-flex justify-content-between mt-1"><span class="text-muted">Fecha</span><span>' + data.venta.fecha_venta + '</span></div>' +
                    '<div class="d-flex justify-content-between mt-1"><span class="text-muted">Cliente</span><span>' + data.venta.cliente + '</span></div>' +
                    '<div class="d-flex justify-content-between mt-1"><span class="text-muted">Total venta</span><strong>S/ ' + data.venta.total.toFixed(2) + '</strong></div>';

                sinVentaMsg.style.display = 'none';
                productosContainer.style.display = 'block';
                productosTbody.innerHTML = '';

                let tieneDisponibles = false;
                data.detalles.forEach(function(det) {
                    if (det.cantidad_disponible <= 0) return;

                    tieneDisponibles = true;
                    const tr = document.createElement('tr');
                    tr.dataset.detalleVentaId = det.detalle_venta_id;
                    tr.dataset.productoId = det.producto_id;
                    tr.dataset.precio = det.precio_unitario;

                    tr.innerHTML = `
                        <td><input type="checkbox" class="form-check-input check-item" data-detalle="${det.detalle_venta_id}"></td>
                        <td>
                            <div style="font-weight:500;">${det.producto_nombre}</div>
                            <div style="font-size:11px;color:#9ca3af;">Precio: S/ ${det.precio_unitario.toFixed(2)}</div>
                            ${det.imei ? `<div style="font-size:11px;color:#9ca3af;">IMEI: ${det.imei}</div>` : ''}
                        </td>
                        <td class="text-center">${det.cantidad_vendida}</td>
                        <td class="text-center">
                            ${det.cantidad_devuelta > 0
                                ? `<span class="badge" style="background:#fef3c7;color:#92400e;">${det.cantidad_devuelta}</span>`
                                : '<span class="text-muted">—</span>'}
                        </td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center cant-item"
                                   min="1" max="${det.cantidad_disponible}" value="${det.cantidad_disponible === 1 ? 1 : ''}"
                                   data-detalle="${det.detalle_venta_id}" disabled>
                            <div style="font-size:10px;color:#9ca3af;">Disponible: ${det.cantidad_disponible}</div>
                        </td>
                        <td>
                            <select class="form-select form-select-sm cond-item" data-detalle="${det.detalle_venta_id}" disabled>
                                <option value="nuevo">🆕 Nuevo</option>
                                <option value="usado">👌 Usado</option>
                                <option value="dañado">💥 Dañado</option>
                                <option value="incompleto">📦 Incompleto</option>
                            </select>
                        </td>
                    `;
                    productosTbody.appendChild(tr);
                });

                if (!tieneDisponibles) {
                    productosTbody.innerHTML = `
                        <tr><td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle me-2" style="color:#10b981;"></i>
                            Todos los productos de esta venta ya fueron devueltos</td></tr>`;
                    btnSubmit.disabled = true;
                }

                checkAll.disabled = false;
                checkAll.checked = false;

                document.querySelectorAll('.check-item').forEach(chk => {
                    chk.addEventListener('change', function() {
                        const fila = this.closest('tr');
                        const cantInput = fila.querySelector('.cant-item');
                        const condSelect = fila.querySelector('.cond-item');

                        cantInput.disabled = !this.checked;
                        condSelect.disabled = !this.checked;

                        if (!this.checked) {
                            cantInput.value = '';
                        } else {
                            const max = parseInt(cantInput.max);
                            if (max === 1) cantInput.value = 1;
                            cantInput.focus();
                        }

                        actualizarTotales();
                        actualizarCheckAll();
                    });
                });

                document.querySelectorAll('.cant-item').forEach(inp => {
                    inp.addEventListener('input', actualizarTotales);
                });

                actualizarTotales();
            })
            .catch(err => {
                console.error('Error al cargar venta:', err);
                alert('Error al cargar los datos de la venta.');
            });
    }

    checkAll.addEventListener('change', function() {
        document.querySelectorAll('.check-item').forEach(chk => {
            chk.checked = this.checked;
            const fila = chk.closest('tr');
            const cantInput = fila.querySelector('.cant-item');
            const condSelect = fila.querySelector('.cond-item');
            cantInput.disabled = !this.checked;
            condSelect.disabled = !this.checked;
            if (this.checked) {
                const max = parseInt(cantInput.max);
                if (max === 1) cantInput.value = 1;
            } else {
                cantInput.value = '';
            }
        });
        actualizarTotales();
    });

    function actualizarCheckAll() {
        const checks = document.querySelectorAll('.check-item');
        if (checks.length === 0) return;
        checkAll.checked = Array.from(checks).every(c => c.checked);
    }

    function actualizarTotales() {
        let subtotal = 0;
        let hayItems = false;

        document.querySelectorAll('.check-item:checked').forEach(chk => {
            const fila = chk.closest('tr');
            const cantInput = fila.querySelector('.cant-item');
            const cantidad = parseInt(cantInput.value) || 0;
            const precio = parseFloat(fila.dataset.precio) || 0;
            const max = parseInt(cantInput.max);

            if (cantidad > max) {
                cantInput.value = max;
                return actualizarTotales();
            }

            if (cantidad > 0) {
                hayItems = true;
                subtotal += precio * cantidad;
            }
        });

        const impuesto = subtotal * (igvPorcentaje / 100);
        const total = subtotal + impuesto;

        document.getElementById('resumenSubtotal').textContent = 'S/ ' + subtotal.toFixed(2);
        document.getElementById('resumenImpuesto').textContent = 'S/ ' + impuesto.toFixed(2);
        document.getElementById('resumenTotal').textContent = 'S/ ' + total.toFixed(2);

        btnSubmit.disabled = !hayItems;
        btnSubmit.style.opacity = hayItems ? '1' : '.5';
    }

    document.getElementById('formDevolucion').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.check-item:checked');
        if (items.length === 0) {
            e.preventDefault();
            alert('Selecciona al menos un producto para devolver.');
            return;
        }

        document.querySelectorAll('.input-devolucion-hidden').forEach(el => el.remove());

        let index = 0;
        items.forEach(chk => {
            const fila = chk.closest('tr');
            const cantidad = parseInt(fila.querySelector('.cant-item').value) || 0;
            if (cantidad <= 0) return;

            const inputs = [
                ['productos[' + index + '][detalle_venta_id]', fila.dataset.detalleVentaId],
                ['productos[' + index + '][producto_id]', fila.dataset.productoId],
                ['productos[' + index + '][cantidad]', cantidad],
                ['productos[' + index + '][condicion]', fila.querySelector('.cond-item').value]
            ];

            inputs.forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                input.className = 'input-devolucion-hidden';
                this.appendChild(input);
            });

            index++;
        });

        if (index === 0) {
            e.preventDefault();
            alert('La cantidad a devolver debe ser mayor a 0.');
        }
    });
});
</script>
@endpush