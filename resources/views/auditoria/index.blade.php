@extends('layouts.app')

@section('title', 'Auditoría')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-clipboard-check me-2 text-primary"></i>Registro de Auditoría</h4>
            <p class="text-muted mb-0" style="font-size:13px;">Historial de acciones realizadas en el sistema</p>
        </div>
    </div>

    {{-- ── KPIs ── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:48px;height:48px;background:#cffafe;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#0e7490;">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:700;color:#0f172a;">{{ $stats['total'] }}</div>
                        <div style="font-size:12px;color:#6b7280;">Total de registros</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:48px;height:48px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#059669;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:700;color:#0f172a;">{{ $stats['hoy'] }}</div>
                        <div style="font-size:12px;color:#6b7280;">Acciones hoy</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:48px;height:48px;background:#e0f2fe;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;color:#0369a1;">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div>
                        <div style="font-size:22px;font-weight:700;color:#0f172a;">{{ $stats['acciones'] }}</div>
                        <div style="font-size:12px;color:#6b7280;">Tipos de acciones</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filtros ── --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="filtro_accion" class="form-label mb-1">Acción</label>
                    <input type="text" name="accion" id="filtro_accion" class="form-control form-control-sm" placeholder="ej: activar_2fa" value="{{ request('accion') }}">
                </div>
                <div class="col-md-3">
                    <label for="filtro_usuario" class="form-label mb-1">Usuario</label>
                    <input type="text" name="usuario" id="filtro_usuario" class="form-control form-control-sm" placeholder="Nombre del usuario" value="{{ request('usuario') }}">
                </div>
                <div class="col-md-2">
                    <label for="filtro_desde" class="form-label mb-1">Desde</label>
                    <input type="date" name="desde" id="filtro_desde" class="form-control form-control-sm" value="{{ request('desde') }}">
                </div>
                <div class="col-md-2">
                    <label for="filtro_hasta" class="form-label mb-1">Hasta</label>
                    <input type="date" name="hasta" id="filtro_hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                    @if(request()->hasAny(['accion','usuario','desde','hasta']))
                        <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── Tabla ── --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Entidad</th>
                            <th>Detalle</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditorias as $aud)
                        <tr>
                            <td class="text-muted">{{ $aud->id }}</td>
                            <td>
                                <div style="font-size:12.5px;font-weight:500;">{{ $aud->created_at->format('d/m/Y') }}</div>
                                <div style="font-size:11px;color:#9ca3af;">{{ $aud->created_at->format('H:i:s') }}</div>
                            </td>
                            <td>
                                @if($aud->usuario)
                                    <div style="font-size:13px;font-weight:500;">{{ $aud->usuario->name }}</div>
                                    <div style="font-size:11px;color:#9ca3af;">{{ $aud->usuario->rol }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-estado" style="background:#cffafe;color:#0e7490;">{{ $aud->accion }}</span>
                            </td>
                            <td>
                                <div style="font-size:12.5px;">{{ $aud->entidad ?? '—' }}</div>
                                @if($aud->entidad_id)
                                    <div style="font-size:11px;color:#9ca3af;">ID: {{ $aud->entidad_id }}</div>
                                @endif
                            </td>
                            <td style="max-width:280px;">
                                <div style="font-size:12px;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $aud->detalle }}">
                                    {{ $aud->detalle ?? '—' }}
                                </div>
                            </td>
                            <td>
                                <code style="font-size:11px;">{{ $aud->ip ?? '—' }}</code>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-40"></i>
                                No hay registros de auditoría
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($auditorias->hasPages())
            <div class="p-3 d-flex justify-content-center">
                {{ $auditorias->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
