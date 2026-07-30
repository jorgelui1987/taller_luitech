@extends('layouts.app')
@section('title', 'Nueva Orden de Compra')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('compras.index') }}" style="color:#a855f7;">Órdenes de Compra</a></li>
    <li class="breadcrumb-item active">Nueva Orden</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Nueva Orden de Compra</h5>
                <p class="text-muted mb-4" style="font-size:13px;">Selecciona el proveedor y los productos a ordenar</p>
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('compras.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                            <select name="proveedor_id" class="form-select" required>
                                <option value="">— Seleccionar —</option>
                                @foreach($proveedores as $p)
                                    <option value="{{ $p->id }}" {{ old('proveedor_id', request('proveedor_id'))==$p->id?'selected':'' }}>{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha estimada</label>
                            <input type="date" class="form-control" name="fecha_estimada" value="{{ old('fecha_estimada') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Descuento general (S/)</label>
                            <input type="number" class="form-control" name="descuento_general" value="{{ old('descuento_general', 0) }}" min="0" step="0.01">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Productos</h6>
                    <div id="productos-container">
                        <div class="row g-2 mb-2 producto-item">
                            <div class="col-md-5">
                                <select name="productos[0][id]" class="form-select" required>
                                    <option value="">— Producto —</option>
                                    @foreach($productos as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->nombre }} ({{ $prod->codigo }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="productos[0][cantidad]" class="form-control" placeholder="Cant." min="1" value="1" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="productos[0][precio]" class="form-control" placeholder="Precio" min="0" step="0.01" value="0" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="productos[0][descuento]" class="form-control" placeholder="Dto." min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.producto-item').remove()" style="border-radius:8px;padding:6px;"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="agregarProducto()">
                        <i class="fas fa-plus me-1"></i>Agregar producto
                    </button>

                    <div class="col-12 mt-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" name="notas" rows="2" placeholder="Notas para la orden...">{{ old('notas') }}</textarea>
                    </div>

                    <hr class="mt-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('compras.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Crear Orden de Compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
let productoIndex = 1;
function agregarProducto() {
    const container = document.getElementById('productos-container');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 producto-item';
    div.innerHTML = `
        <div class="col-md-5">
            <select name="productos[${productoIndex}][id]" class="form-select" required>
                <option value="">— Producto —</option>
                @foreach($productos as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->nombre }} ({{ $prod->codigo }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><input type="number" name="productos[${productoIndex}][cantidad]" class="form-control" placeholder="Cant." min="1" value="1" required></div>
        <div class="col-md-2"><input type="number" name="productos[${productoIndex}][precio]" class="form-control" placeholder="Precio" min="0" step="0.01" value="0" required></div>
        <div class="col-md-2"><input type="number" name="productos[${productoIndex}][descuento]" class="form-control" placeholder="Dto." min="0" step="0.01" value="0"></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.producto-item').remove()" style="border-radius:8px;padding:6px;"><i class="fas fa-times"></i></button></div>
    `;
    container.appendChild(div);
    productoIndex++;
}
</script>
@endpush