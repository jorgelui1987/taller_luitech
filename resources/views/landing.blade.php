@extends('layouts.public')

@section('title', ($empresa->nombre_tienda ?? 'LUITECH') . ' · Servicio Técnico de Celulares y Computadores')

@section('content')
<section class="lp-hero">
    <div class="lp-container lp-hero-grid">
        <div>
            <span class="lp-hero-chip"><i class="fa-solid fa-location-dot"></i> {{ $empresa->direccion ?? 'Atención personalizada' }}</span>
            <h1 class="lp-hero-title">Tu tecnología en manos de <span class="lp-hero-grad">expertos</span>.</h1>
            <p class="lp-hero-text">Reparación de celulares, tablets, notebooks y PC con repuestos de calidad, garantía escrita y seguimiento en línea de tu orden, paso a paso.</p>
            <div class="lp-hero-actions">
                <a href="#consulta" class="lp-btn lp-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Consultar mi orden</a>
                <a href="#servicios" class="lp-btn lp-btn-ghost"><i class="fa-solid fa-screwdriver-wrench"></i> Ver servicios</a>
            </div>
        </div>

        <div class="lp-card" id="consulta">
            <span class="lp-card-corner">Rápido</span>
            <h3><i class="fa-solid fa-route"></i> Consulta Express</h3>
            <p>¿Dejaste tu equipo con nosotros? Ingresa el código de tu boleta y mira el avance actual de tu reparación.</p>
            <form method="GET" action="{{ route('reparaciones.public-status.search') }}">
                <label class="lp-label" for="express-code">Código de reparación</label>
                <div class="lp-input-group">
                    <span class="lp-input-prefix">RPT-</span>
                    <input class="lp-input" type="text" id="express-code" name="numero_orden" placeholder="001024" inputmode="numeric" autocomplete="off" required>
                </div>
                <button type="submit" class="lp-btn lp-btn-primary lp-btn-block"><i class="fa-solid fa-search"></i> Consultar estado</button>
                <p class="lp-hint">El código completo se ve así: <strong>RPT-001024</strong>. También puedes pegarlo completo.</p>
            </form>
        </div>
    </div>
</section>
<section id="servicios" class="lp-section">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">¿Qué ofrecemos?</span>
            <h2>Nuestras especialidades</h2>
            <p>Diagnóstico honesto, repuestos de calidad y garantía escrita en cada trabajo.</p>
        </div>
        <div class="lp-features">
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <h4>Pantallas y cristales</h4>
                <p>Reemplazo de pantallas OLED/IPS, vidrios templados e hidrogel para smartphones y tablets de todas las marcas.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-battery-full"></i></div>
                <h4>Baterías y carga</h4>
                <p>Cambio de baterías, puertos de carga, fallas de energía y accesorios certificados.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-microchip"></i></div>
                <h4>Placas y software</h4>
                <p>Reparación de placa a nivel componente, recuperación de software, sistemas y datos.</p>
            </div>
        </div>
    </div>
</section>

<section id="como-funciona" class="lp-section lp-section-alt">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Transparencia total</span>
            <h2>¿Cómo funciona?</h2>
            <p>Sabes qué pasa con tu equipo en todo momento, sin llamadas ni incertidumbre.</p>
        </div>
        <div class="lp-features">
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-receipt"></i></div>
                <span class="lp-feature-n">PASO 1</span>
                <h4>Deja tu equipo y guarda tu código</h4>
                <p>Al recibir tu equipo entregamos una boleta con el código de la orden de reparación.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-stethoscope"></i></div>
                <span class="lp-feature-n">PASO 2</span>
                <h4>Diagnóstico y presupuesto</h4>
                <p>Registramos la falla, el diagnóstico técnico y el presupuesto antes de tocar un solo tornillo.</p>
            </div>
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-bell"></i></div>
                <span class="lp-feature-n">PASO 3</span>
                <h4>Aviso de retiro</h4>
                <p>Te notificamos cuando esté listo. Presenta tu boleta o el código en el mesón para retirarlo.</p>
            </div>
        </div>
    </div>
</section>
<section class="lp-section">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Para emprendedores</span>
            <h2>¿Tienes una tienda de celulares?</h2>
            <p>Este mismo sistema gestiona ventas, inventario, reparaciones, comisiones y reportes. Pruébalo gratis.</p>
        </div>
        <div class="lp-hero-actions" style="justify-content:center;">
            <a href="{{ route('registro.tenant') }}" class="lp-btn lp-btn-primary"><i class="fa-solid fa-store"></i> Registra tu tienda gratis</a>
            <a href="{{ route('planes') }}" class="lp-btn lp-btn-ghost"><i class="fa-solid fa-tags"></i> Ver planes</a>
            <a href="{{ route('login') }}" class="lp-btn lp-btn-ghost"><i class="fa-solid fa-right-to-bracket"></i> Acceso del personal</a>
        </div>
    </div>
</section>

<section id="contacto" class="lp-section lp-section-alt">
    <div class="lp-container">
        <div class="lp-section-head">
            <span class="lp-section-chip">Visítanos</span>
            <h2>Contacto</h2>
            <p>Estamos para ayudarte con tu equipo. Escríbenos o ven al local.</p>
        </div>
        <div class="lp-features">
            @if(!empty($empresa->direccion))
                <div class="lp-feature">
                    <div class="lp-feature-ico"><i class="fa-solid fa-location-dot"></i></div>
                    <h4>Dirección</h4>
                    <p>{{ $empresa->direccion }}</p>
                </div>
            @endif
            @if(!empty($empresa->telefono))
                @php
                    $waDigits = preg_replace('/\D/', '', (string) $empresa->telefono);
                    $waNumber = $waDigits !== '' ? (str_starts_with($waDigits, '56') ? $waDigits : '56' . $waDigits) : null;
                @endphp
                <div class="lp-feature">
                    <div class="lp-feature-ico"><i class="fa-brands fa-whatsapp"></i></div>
                    <h4>WhatsApp</h4>
                    <p><a class="lp-wa" href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode('Hola, tengo una consulta sobre mi equipo') }}" target="_blank" rel="noopener">+{{ $waNumber }}</a></p>
                </div>
            @endif
            <div class="lp-feature">
                <div class="lp-feature-ico"><i class="fa-solid fa-tv"></i></div>
                <h4>Sala de espera</h4>
                <p>Sigue los turnos en vivo en la pantalla del local o desde <a href="{{ route('public.pantalla') }}" target="_blank" rel="noopener">tu celular</a>.</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    // Solo caracteres válidos en el código express
    const lpCode = document.getElementById('express-code');
    if (lpCode) {
        lpCode.addEventListener('input', () => {
            lpCode.value = lpCode.value.replace(/[^0-9A-Za-z-]/g, '').toUpperCase();
        });
    }
@endpush