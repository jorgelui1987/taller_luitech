@extends('layouts.app')

@section('title', 'Tablero Kanban')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Tablero Kanban</h4>
        <p class="text-muted mb-0" style="font-size:13px;">Arrastra las tarjetas para cambiar el estado</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reparaciones.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>Vista Lista
        </a>
        <a href="{{ route('reparaciones.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Reparación
        </a>
    </div>
</div>

<style>
.kanban-board {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 20px;
    min-height: 70vh;
}
.kanban-column {
    min-width: 280px;
    max-width: 280px;
    flex-shrink: 0;
    background: #f3f4f6;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 200px);
}
.kanban-column-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 12px;
    font-weight: 600;
    font-size: 13px;
}
.kanban-column-body {
    flex: 1;
    overflow-y: auto;
    min-height: 100px;
}
.kanban-card {
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    cursor: grab;
    transition: all .2s;
    border-left: 4px solid #e5e7eb;
}
.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.12);
    transform: translateY(-2px);
}
.kanban-card.dragging {
    opacity: .5;
    transform: scale(.95);
}
.kanban-card .card-title {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 4px;
}
.kanban-card .card-sub {
    font-size: 11px;
    color: #6b7280;
}
.kanban-card .card-badges {
    display: flex;
    gap: 4px;
    margin-top: 8px;
    flex-wrap: wrap;
}
.kanban-card .badge-prioridad {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 600;
}
.kanban-column.drag-over {
    background: #e0e7ff;
    border: 2px dashed #6366f1;
}
</style>

<div class="kanban-board">
    @foreach($estados as $estadoKey => $estadoInfo)
    <div class="kanban-column" data-estado="{{ $estadoKey }}" id="col-{{ $estadoKey }}">
        <div class="kanban-column-header" style="background:{{ $estadoInfo['color'] }};">
            <span><i class="fas {{ $estadoInfo['icon'] }} me-2"></i>{{ $estadoInfo['label'] }}</span>
            <span class="badge bg-white text-dark">{{ $reparaciones->where('estado', $estadoKey)->count() }}</span>
        </div>
        <div class="kanban-column-body">
            @foreach($reparaciones->where('estado', $estadoKey) as $rep)
            <div class="kanban-card" draggable="true" data-id="{{ $rep->id }}" data-estado="{{ $estadoKey }}">
                <div class="card-title">
                    <a href="{{ route('reparaciones.show', $rep) }}" style="color:#1e1b4b;text-decoration:none;">
                        {{ $rep->dispositivo ?: 'Dispositivo' }}
                    </a>
                </div>
                <div class="card-sub">
                    {{ $rep->marca }} {{ $rep->modelo }}<br>
                    <strong>{{ $rep->numero_orden }}</strong> · {{ $rep->cliente->nombre_completo ?? '—' }}
                </div>
                <div class="card-badges">
                    @php
                        $prioridadColors = ['urgente'=>'#fee2e2;color:#991b1b', 'alta'=>'#fef3c7;color:#92400e', 'media'=>'#e0f2fe;color:#0369a1', 'baja'=>'#f3f4f6;color:#374151'];
                        $pc = $prioridadColors[$rep->prioridad] ?? '#f3f4f6;color:#374151';
                    @endphp
                    <span class="badge-prioridad" style="background:{{ explode(';', $pc)[0] }};color:{{ explode(';', $pc)[1] }};">
                        {{ ucfirst($rep->prioridad) }}
                    </span>
                    @if($rep->fecha_estimada)
                        <span class="badge-prioridad" style="background:#e0e7ff;color:#3730a3;">
                            <i class="fas fa-calendar me-1"></i>{{ $rep->fecha_estimada->format('d/m') }}
                        </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column');

    let draggedCard = null;

    cards.forEach(card => {
        card.addEventListener('dragstart', function(e) {
            draggedCard = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        card.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            draggedCard = null;
            columns.forEach(col => col.classList.remove('drag-over'));
        });
    });

    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        column.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });

        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (!draggedCard) return;

            const nuevoEstado = this.dataset.estado;
            const reparacionId = draggedCard.dataset.id;
            const estadoAnterior = draggedCard.dataset.estado;

            if (nuevoEstado === estadoAnterior) return;

            // Mover la tarjeta visualmente
            const body = this.querySelector('.kanban-column-body');
            body.appendChild(draggedCard);
            draggedCard.dataset.estado = nuevoEstado;

            // Actualizar contadores
            const colAnterior = document.getElementById('col-' + estadoAnterior);
            const colNueva = document.getElementById('col-' + nuevoEstado);
            actualizarContadores(colAnterior);
            actualizarContadores(colNueva);

            // Enviar al servidor
            fetch(`/reparaciones/${reparacionId}/kanban-estado`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ estado: nuevoEstado })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.whatsapp_url) {
                        if (confirm('¿Enviar notificación por WhatsApp al cliente?')) {
                            window.open(data.whatsapp_url, '_blank');
                        }
                    }
                    if (data.cupon) {
                        alert('🎉 Cupón generado: ' + data.cupon);
                    }
                }
            })
            .catch(err => {
                console.error('Error:', err);
                // Revertir visualmente
                const colOriginal = document.getElementById('col-' + estadoAnterior);
                colOriginal.querySelector('.kanban-column-body').appendChild(draggedCard);
                draggedCard.dataset.estado = estadoAnterior;
                actualizarContadores(colOriginal);
                actualizarContadores(this);
            });
        });
    });

    function actualizarContadores(columna) {
        const count = columna.querySelectorAll('.kanban-card').length;
        const badge = columna.querySelector('.kanban-column-header .badge');
        if (badge) badge.textContent = count;
    }
});
</script>
@endpush