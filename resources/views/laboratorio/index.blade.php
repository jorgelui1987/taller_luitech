@extends('layouts.app')

@section('title', 'Laboratorio Digital')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <h4 class="fw-bold mb-0"><i class="fas fa-flask me-2" style="color:var(--accent1);"></i> Laboratorio Digital</h4>
        <a href="{{ url('/') }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt me-1"></i> Ver portada</a>
    </div>
    <p class="text-muted small">Este contenido se muestra en la portada pública: el cotizador de precios y las preguntas frecuentes. Los cambios se ven al instante, sin necesidad de desplegar.</p>

    @if(session('success'))
        <div class="alert alert-success py-2"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</div>
    @endif

    {{-- ══ COTIZADOR ══ --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white fw-bold"><i class="fas fa-calculator me-2" style="color:var(--accent1);"></i> Cotizador — Precios por servicio y dispositivo</div>
        <div class="card-body">
            <form method="POST" action="{{ route('laboratorio.precios.guardar') }}">
                @csrf
                <div class="table-responsive-custom">
                    <table class="table table-sm table-hover align-middle mb-2">
                        <thead>
                            <tr>
                                <th>Servicio (visible)</th>
                                <th>Dispositivo (visible)</th>
                                <th style="width:130px;">Precio mín.</th>
                                <th style="width:130px;">Precio máx.</th>
                                <th class="text-center" style="width:80px;">Activo</th>
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($precios as $p)
                            <tr>
                                <td><input name="precios[{{ $p->id }}][servicio_label]" value="{{ $p->servicio_label }}" class="form-control form-control-sm"></td>
                                <td><input name="precios[{{ $p->id }}][dispositivo_label]" value="{{ $p->dispositivo_label }}" class="form-control form-control-sm"></td>
                                <td><input type="number" min="0" name="precios[{{ $p->id }}][precio_min]" value="{{ $p->precio_min }}" class="form-control form-control-sm"></td>
                                <td><input type="number" min="0" name="precios[{{ $p->id }}][precio_max]" value="{{ $p->precio_max }}" class="form-control form-control-sm"></td>
                                <td class="text-center"><input class="form-check-input" type="checkbox" name="precios[{{ $p->id }}][activo]" value="1" @checked($p->activo)></td>
                                <td class="text-end">
                                    <button form="form-eliminar-precio-{{ $p->id }}" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Guardar precios</button>
            </form>
            @foreach($precios as $p)
                <form id="form-eliminar-precio-{{ $p->id }}" method="POST" action="{{ route('laboratorio.precios.eliminar', $p->id) }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

            <hr>
            <form method="POST" action="{{ route('laboratorio.precios.agregar') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small mb-1">Servicio nuevo (visible)</label>
                    <input name="servicio_label" class="form-control form-control-sm" placeholder="Cambio de Cristal" required>
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small mb-1">Clave (minúsculas, sin espacios)</label>
                    <input name="servicio" class="form-control form-control-sm" placeholder="cristal" pattern="[a-z0-9_]+" required>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small mb-1">Dispositivo</label>
                    <select name="dispositivo" class="form-select form-select-sm">
                        <option value="celular">Celular / Smartphone</option>
                        <option value="tablet">Tablet</option>
                        <option value="notebook">Notebook</option>
                        <option value="pc">PC de Escritorio</option>
                        <option value="consola">Consola</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small mb-1">Precio mín.</label>
                    <input type="number" min="0" name="precio_min" class="form-control form-control-sm" placeholder="20000" required>
                </div>
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small mb-1">Precio máx.</label>
                    <input type="number" min="0" name="precio_max" class="form-control form-control-sm" placeholder="40000" required>
                </div>
                <div class="col-lg-1 col-md-12">
                    <button class="btn btn-sm btn-success w-100" title="Agregar"><i class="fas fa-plus"></i></button>
                </div>
            </form>
            <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i> Para agregar un servicio nuevo a todos los dispositivos: agrégalo una vez por cada dispositivo (misma clave). Los precios van sin puntos: 45000 = $45.000.</p>
        </div>
    </div>
    {{-- ══ FAQ ══ --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold"><i class="fas fa-circle-question me-2" style="color:var(--accent1);"></i> Preguntas frecuentes (FAQ de la portada)</div>
        <div class="card-body">
            <form method="POST" action="{{ route('laboratorio.faq.guardar') }}">
                @csrf
                @foreach($faqs as $f)
                    <div class="border rounded p-2 mb-2">
                        <input name="faqs[{{ $f->id }}][pregunta]" value="{{ $f->pregunta }}" class="form-control form-control-sm fw-bold mb-1" placeholder="Pregunta">
                        <textarea name="faqs[{{ $f->id }}][respuesta]" class="form-control form-control-sm mb-1" rows="2" placeholder="Respuesta">{{ $f->respuesta }}</textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="small mb-0"><input class="form-check-input me-1" type="checkbox" name="faqs[{{ $f->id }}][activo]" value="1" @checked($f->activo)> Activa</label>
                            <button form="form-eliminar-faq-{{ $f->id }}" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                @endforeach
                <button class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Guardar preguntas</button>
            </form>

            @foreach($faqs as $f)
                <form id="form-eliminar-faq-{{ $f->id }}" method="POST" action="{{ route('laboratorio.faq.eliminar', $f->id) }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

            <hr>
            <form method="POST" action="{{ route('laboratorio.faq.agregar') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small mb-1">Nueva pregunta</label>
                    <input name="pregunta" class="form-control form-control-sm" placeholder="¿Hacen compras de equipos usados?" required>
                </div>
                <div class="col-md-7">
                    <label class="form-label small mb-1">Respuesta</label>
                    <input name="respuesta" class="form-control form-control-sm" placeholder="Sí, evaluamos tu equipo y te ofrecemos el mejor precio." required>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-success w-100" title="Agregar"><i class="fas fa-plus"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection