@extends('layouts.app')
@section('title', 'Ajuste de Inventario')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('productos.index') }}" style="color:#a855f7;">Inventario</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.movimientos') }}" style="color:#a855f7;">Movimientos</a></li>
    <li class="breadcrumb-item active">Nuevo Ajuste</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Ajuste de Inventario</h5>
                <p class="text-muted mb-4" style="font-size:13px;">
                    Registra entradas, salidas o ajustes manuales de stock
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('stock.ajuste.store') }}" method="POST">
                    @csrf

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label">Producto <span class="text-danger">*</span></label>
                            <select name="producto_id" class="form-select @error('producto_id') is-invalid @enderror" required
                                    onchange="actualizarStockActual(this)">
                                <option value="">— Seleccionar producto —</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id }}"
                                            data-stock="{{ $p->stock }}"
                                            {{ old('producto_id')==$p->id?'selected':'' }}>
                                        {{ $p->nombre }} ({{ $p->codigo }}) — Stock: {{ $p->stock }}
                                    </option>
                                @endforeach
                            </select>
                            @error('producto_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Stock Actual</label>
                            <div class="form-control" id="stockActualDisplay" style="background:#f9fafb; font-weight:600; font-size:18px;">
                                —
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Stock Resultante</label>
                            <div class="form-control" id="stockResultanteDisplay" style="background:#f9fafb; font-weight:600; font-size:18px; color:#7c3aed;">
                                —
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required
                                    onchange="toggleMotivos()">
                                <option value="">— Seleccionar —</option>
                                <option value="entrada" {{ old('tipo')=='entrada'?'selected':'' }}>📦 Entrada (agregar stock)</option>
                                <option value="salida" {{ old('tipo')=='salida'?'selected':'' }}>📤 Salida (quitar stock)</option>
                                <option value="ajuste" {{ old('tipo')=='ajuste'?'selected':'' }}>⚖️ Ajuste manual</option>
                            </select>
                            @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Motivo <span class="text-danger">*</span></label>
                            <select name="motivo" class="form-select @error('motivo') is-invalid @enderror" required id="motivoSelect">
                                <option value="">— Seleccionar —</option>
                                <optgroup label="Entradas" id="motivosEntrada">
                                    <option value="compra" {{ old('motivo')=='compra'?'selected':'' }}>Compra / Reposición</option>
                                    <option value="devolucion" {{ old('motivo')=='devolucion'?'selected':'' }}>Devolución de cliente</option>
                                    <option value="sobrante" {{ old('motivo')=='sobrante'?'selected':'' }}>Sobrante en inventario</option>
                                </optgroup>
                                <optgroup label="Salidas" id="motivosSalida">
                                    <option value="perdida" {{ old('motivo')=='perdida'?'selected':'' }}>Pérdida</option>
                                    <option value="daño" {{ old('motivo')=='daño'?'selected':'' }}>Dañado / Defectuoso</option>
                                </optgroup>
                                <optgroup label="Ajustes" id="motivosAjuste">
                                    <option value="ajuste" {{ old('motivo')=='ajuste'?'selected':'' }}>Ajuste manual</option>
                                </optgroup>
                            </select>
                            @error('motivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('cantidad') is-invalid @enderror"
                                   name="cantidad" value="{{ old('cantidad', 1) }}"
                                   min="1" step="1" id="cantidadInput"
                                   oninput="calcularStockResultante()">
                            @error('cantidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="stockAdvertencia" class="text-danger mt-1" style="font-size:12px; display:none;">
                                ⚠️ La cantidad supera el stock actual
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observación</label>
                            <textarea class="form-control" name="observacion" rows="2"
                                      placeholder="Detalla el motivo del ajuste...">{{ old('observacion') }}</textarea>
                        </div>
                    </div>

                    <hr class="mt-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('stock.movimientos') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Registrar Ajuste
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let productosData = [];

document.addEventListener('DOMContentLoaded', function() {
    // Cargar datos de productos
    document.querySelectorAll('[name=producto_id] option').forEach(opt => {
        if (opt.value) {
            productosData[opt.value] = parseInt(opt.dataset.stock) || 0;
        }
    });
    toggleMotivos();
    calcularStockResultante();
});

function actualizarStockActual(select) {
    calcularStockResultante();
}

function toggleMotivos() {
    const tipo = document.querySelector('[name=tipo]').value;
    document.querySelectorAll('#motivosEntrada, #motivosSalida, #motivosAjuste').forEach(g => g.style.display = 'none');

    if (tipo === 'entrada') document.getElementById('motivosEntrada').style.display = '';
    else if (tipo === 'salida') document.getElementById('motivosSalida').style.display = '';
    else if (tipo === 'ajuste') document.getElementById('motivosAjuste').style.display = '';
}

function calcularStockResultante() {
    const select = document.querySelector('[name=producto_id]');
    const cantidad = parseInt(document.getElementById('cantidadInput').value) || 0;
    const tipo = document.querySelector('[name=tipo]').value;
    const stockActualEl = document.getElementById('stockActualDisplay');
    const stockResultanteEl = document.getElementById('stockResultanteDisplay');
    const advertencia = document.getElementById('stockAdvertencia');

    if (!select.value) {
        stockActualEl.textContent = '—';
        stockResultanteEl.textContent = '—';
        return;
    }

    const stockActual = productosData[select.value] || 0;
    stockActualEl.textContent = stockActual;

    let stockResultante = stockActual;
    if (tipo === 'entrada') stockResultante = stockActual + cantidad;
    else if (tipo === 'salida') stockResultante = stockActual - cantidad;
    else if (tipo === 'ajuste') stockResultante = stockActual + cantidad; // se maneja con signo

    stockResultanteEl.textContent = stockResultante;

    if (stockResultante < 0) {
        stockResultanteEl.style.color = '#dc2626';
        advertencia.style.display = 'block';
    } else {
        stockResultanteEl.style.color = '#7c3aed';
        advertencia.style.display = 'none';
    }
}
</script>
@endpush