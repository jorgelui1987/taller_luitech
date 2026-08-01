@extends('layouts.app')
@section('title', 'Gastos Fijos')

@section('breadcrumb')
    <li class="breadcrumb-item active">Gastos Fijos</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Gastos Fijos</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Total mensual: <strong style="color:#dc2626;">{{ formato_moneda($totalMensual) }}</strong>
        </p>
    </div>
    <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#modalNuevoGasto">
        <i class="fas fa-plus me-2"></i>Nuevo Gasto
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th class="ps-4">Gasto</th>
                        <th>Categoría</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gastos as $gasto)
                    <tr>
                        <td class="ps-4">
                            <div style="font-weight:600;">{{ $gasto->nombre }}</div>
                            @if($gasto->descripcion)
                                <div style="font-size:11px; color:#9ca3af;">{{ $gasto->descripcion }}</div>
                            @endif
                        </td>
                        <td>
                            @if($gasto->categoria)
                                <span style="background:#ede9fe; color:#7c3aed; border-radius:20px; padding:3px 10px; font-size:11px;">
                                    {{ $gasto->categoria }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="font-weight:700; color:#dc2626;">{{ formato_moneda($gasto->monto) }}</td>
                        <td style="font-size:12px;">{{ $gasto->fecha ? $gasto->fecha->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if($gasto->activo)
                                <span style="background:#d1fae5; color:#065f46; border-radius:20px; padding:3px 10px; font-size:11px;">
                                    <i class="fas fa-check me-1"></i>Activo
                                </span>
                            @else
                                <span style="background:#fee2e2; color:#991b1b; border-radius:20px; padding:3px 10px; font-size:11px;">
                                    <i class="fas fa-ban me-1"></i>Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm" style="background:#ede9fe; color:#7c3aed; border-radius:8px; padding:5px 10px;"
                                        onclick="editarGasto({{ $gasto->id }}, '{{ addslashes($gasto->nombre) }}', '{{ $gasto->monto }}', '{{ $gasto->categoria }}', '{{ $gasto->descripcion }}', '{{ $gasto->fecha ? $gasto->fecha->format('Y-m-d') : '' }}')">
                                    <i class="fas fa-edit fa-sm"></i>
                                </button>
                                <form action="{{ route('gastos.toggle', $gasto) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $gasto->activo ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            style="border-radius:8px; padding:5px 10px;">
                                        <i class="fas fa-{{ $gasto->activo ? 'ban' : 'check' }} fa-sm"></i>
                                    </button>
                                </form>
                                <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar este gasto fijo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px; padding:5px 10px;">
                                        <i class="fas fa-trash fa-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-receipt fa-3x mb-3 d-block" style="color:#d1d5db;"></i>
                            <p class="text-muted mb-0">No hay gastos fijos registrados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nuevo Gasto -->
<div class="modal fade" id="modalNuevoGasto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px;">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-receipt me-2" style="color:#a855f7;"></i>Nuevo Gasto Fijo
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gastos.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del gasto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Renta del local" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto (S/) <span class="text-danger">*</span></label>
                            <input type="number" name="monto" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-select">
                                <option value="">— Seleccionar —</option>
                                <option value="Alquiler">🏠 Alquiler</option>
                                <option value="Servicios">💡 Servicios (Luz, Agua)</option>
                                <option value="Internet">📶 Internet</option>
                                <option value="Sueldos">👥 Sueldos</option>
                                <option value="Publicidad">📢 Publicidad</option>
                                <option value="Aseo">🧹 Aseo</option>
                                <option value="Otros">📦 Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="2" placeholder="Detalle del gasto..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Guardar Gasto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Gasto -->
<div class="modal fade" id="modalEditarGasto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:20px 24px;">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-edit me-2" style="color:#a855f7;"></i>Editar Gasto Fijo
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarGasto" action="" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del gasto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="editNombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto (S/) <span class="text-danger">*</span></label>
                            <input type="number" name="monto" id="editMonto" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" id="editCategoria" class="form-select">
                                <option value="">— Seleccionar —</option>
                                <option value="Alquiler">🏠 Alquiler</option>
                                <option value="Servicios">💡 Servicios (Luz, Agua)</option>
                                <option value="Internet">📶 Internet</option>
                                <option value="Sueldos">👥 Sueldos</option>
                                <option value="Publicidad">📢 Publicidad</option>
                                <option value="Aseo">🧹 Aseo</option>
                                <option value="Otros">📦 Otros</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" id="editFecha" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editDescripcion" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:16px 24px;">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editarGasto(id, nombre, monto, categoria, descripcion, fecha) {
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editMonto').value = monto;
    document.getElementById('editCategoria').value = categoria || '';
    document.getElementById('editDescripcion').value = descripcion || '';
    document.getElementById('editFecha').value = fecha || '';
    document.getElementById('formEditarGasto').action = '/gastos/' + id;
    var modal = new bootstrap.Modal(document.getElementById('modalEditarGasto'));
    modal.show();
}
</script>
@endpush