@extends('layouts.app')
@section('title', 'Proveedores')
@section('breadcrumb')
    <li class="breadcrumb-item active">Proveedores</li>
@endsection
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div><h4 class="mb-1 fw-bold">Proveedores</h4><p class="text-muted mb-0" style="font-size:13px;">{{ $proveedores->total() }} proveedores registrados</p></div>
    <a href="{{ route('proveedores.create') }}" class="btn btn-primary px-4"><i class="fas fa-plus me-2"></i>Nuevo Proveedor</a>
</div>
<div class="card"><div class="card-body p-0"><div class="table-responsive">
<table class="table table-hover align-middle mb-0"><thead><tr>
    <th class="ps-4">Nombre</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th>RUC</th><th>Órdenes</th><th>Estado</th><th class="text-end pe-4">Acciones</th>
</tr></thead><tbody>
@forelse($proveedores as $p)
<tr>
    <td class="ps-4"><div style="font-weight:500; font-size:13.5px;">{{ $p->nombre }}</div></td>
    <td style="font-size:13px;">{{ $p->contacto ?: '—' }}</td>
    <td style="font-size:13px;">{{ $p->telefono ?: '—' }}</td>
    <td style="font-size:13px;">{{ $p->email ?: '—' }}</td>
    <td style="font-size:13px;">{{ $p->ruc ?: '—' }}</td>
    <td><span style="background:#ede9fe;color:#7c3aed;border-radius:20px;padding:3px 10px;font-size:12px;">{{ $p->ordenesCompra->count() }}</span></td>
    <td>@if($p->activo)<span style="background:#d1fae5;color:#065f46;border-radius:20px;padding:3px 10px;font-size:11px;">Activo</span>@else<span style="background:#fee2e2;color:#dc2626;border-radius:20px;padding:3px 10px;font-size:11px;">Inactivo</span>@endif</td>
    <td class="text-end pe-4"><div class="d-flex gap-1 justify-content-end">
        <a href="{{ route('proveedores.show', $p) }}" class="btn btn-sm" style="background:#f3f4f6;color:#374151;border-radius:8px;padding:5px 10px;" title="Ver"><i class="fas fa-eye fa-sm"></i></a>
        <a href="{{ route('proveedores.edit', $p) }}" class="btn btn-sm" style="background:#ede9fe;color:#7c3aed;border-radius:8px;padding:5px 10px;" title="Editar"><i class="fas fa-edit fa-sm"></i></a>
        <form action="{{ route('proveedores.toggle', $p) }}" method="POST" style="display:inline;">@csrf <button type="submit" class="btn btn-sm" style="background:#fef3c7;color:#92400e;border-radius:8px;padding:5px 10px;" title="{{ $p->activo ? 'Desactivar' : 'Activar' }}"><i class="fas fa-{{ $p->activo ? 'ban' : 'check' }} fa-sm"></i></button></form>
        @if($p->ordenesCompra->count() == 0)
        <form action="{{ route('proveedores.destroy', $p) }}" method="POST" onsubmit="return confirm('¿Eliminar {{ addslashes($p->nombre) }}?')" style="display:inline;">@csrf @method('DELETE')<button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#dc2626;border-radius:8px;padding:5px 10px;" title="Eliminar"><i class="fas fa-trash fa-sm"></i></button></form>
        @endif
    </div></td>
</tr>
@empty<tr><td colspan="8" class="text-center py-5"><i class="fas fa-truck fa-3x mb-3 d-block" style="color:#d1d5db;"></i><p class="text-muted mb-2">No hay proveedores registrados</p><a href="{{ route('proveedores.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Agregar proveedor</a></td></tr>
@endforelse
</tbody></table></div>
@if($proveedores->hasPages())<div class="p-3 border-top">{{ $proveedores->links() }}</div>@endif
</div></div>
@endsection