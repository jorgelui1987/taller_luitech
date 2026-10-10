<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $config->nombre_tienda ?? $tenant->empresa ?? 'Tienda' }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root{--bg:#0f172a;--deep:#020617;--card:rgba(2,6,23,.72);--border:#1e293b;--text:#f1f5f9;--muted:#94a3b8;--cyan:#22d3ee;--emerald:#34d399;--grad:linear-gradient(135deg,#06b6d4,#3b82f6);}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:'Inter',system-ui,sans-serif;}
.wrap{max-width:1150px;margin:0 auto;padding:0 20px}
.top{position:sticky;top:0;z-index:50;background:rgba(2,6,23,.92);backdrop-filter:blur(8px);border-bottom:1px solid var(--border);}
.top-in{height:66px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.brand{display:flex;align-items:center;gap:12px}.logo{width:42px;height:42px;border-radius:12px;background:var(--grad);display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;overflow:hidden}.logo img{width:100%;height:100%;object-fit:cover}
.bname{font-weight:800;letter-spacing:.06em}.bsub{font-size:11px;color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:12px;font-weight:700;font-size:14px;border:1px solid transparent;text-decoration:none;cursor:pointer}
.btn-p{background:var(--grad);color:#fff}.btn-g{background:#111c33;border-color:var(--border);color:#e2e8f0}.btn-wa{background:#25d366;color:#fff}
.hero{padding:52px 0 40px;background:radial-gradient(ellipse 70% 60% at 50% -10%,rgba(6,182,212,.16),transparent 60%),var(--bg);}
.chip{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:700;color:var(--cyan);background:#12203a;border:1px solid #27405f;padding:6px 14px;border-radius:999px}
.h1{font-size:clamp(28px,5vw,46px);font-weight:900;line-height:1.1;margin:16px 0 10px}.h1 span{background:linear-gradient(90deg,#22d3ee,#3b82f6);-webkit-background-clip:text;background-clip:text;color:transparent}
.lead{color:var(--muted);font-size:15px;line-height:1.6;max-width:620px}
.cta{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}
.sec{padding:26px 0}.card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:24px}
.grid4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@@media(max-width:900px){.grid4{grid-template-columns:1fr 1fr}.grid2{grid-template-columns:1fr}}
.srv{background:rgba(2,6,23,.6);border:1px solid var(--border);border-radius:14px;padding:18px;text-align:center}
.srv .ic{font-size:28px;margin-bottom:8px}.srv b{font-size:14px}.srv small{color:var(--muted);font-size:12px}
.prod{background:rgba(2,6,23,.6);border:1px solid var(--border);border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:6px}
.price{color:var(--cyan);font-weight:900;font-size:20px}.stock{color:var(--emerald);font-size:12px;font-weight:700}
.cupon{border:1.5px dashed var(--emerald);background:rgba(6,78,59,.25);border-radius:14px;padding:16px;text-align:center}
.stars{color:#fbbf24;letter-spacing:2px;font-size:13px}
input.in{width:100%;max-width:280px;padding:12px 14px;border-radius:12px;border:1px solid var(--border);background:#0a1120;color:#fff}
.foot{border-top:1px solid var(--border);background:var(--deep);padding:28px 0;margin-top:20px;color:var(--muted);font-size:13px;text-align:center}
.wa-float{position:fixed;bottom:22px;right:22px;width:56px;height:56px;border-radius:50%;background:#25d366;color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;text-decoration:none;box-shadow:0 8px 24px rgba(37,211,102,.45);z-index:90}
</style>
</head>
<body>
<header class="top"><div class="wrap top-in">
<div class="brand"><div class="logo">@if(!empty($logoSrc))<img src="{{ $logoSrc }}">@else<i class="fa-solid fa-store"></i>@endif</div>
<div><div class="bname">{{ $config->nombre_tienda ?? $tenant->empresa }}</div><div class="bsub">luitech.fun/t/{{ $tenant->slug_publico }}</div></div></div>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
@if(isset($promedio) && $promedio)<span class="chip"><i class="fa-solid fa-star"></i> {{ number_format($promedio,1) }}</span>@endif
@if($config->horario_atencion)<span class="chip"><span style="width:8px;height:8px;border-radius:50%;background:var(--emerald);display:inline-block"></span> {{ $config->horario_atencion }}</span>@endif
</div></div></header>
<section class="hero"><div class="wrap">
<span class="chip"><i class="fa-solid fa-microchip"></i> Servicio técnico · Ventas · Accesorios</span>
<h1 class="h1">Tu celular como nuevo, <span>el mismo día</span>.</h1>
<p class="lead">@if($config->descripcion_corta){{ $config->descripcion_corta }}<br>@endif@if($config->direccion){{ $config->direccion }} · @endif@if(isset($promedio) && $promedio) {{ number_format($promedio,1) }} estrellas ({{ $resenas->count() }} reseñas)@endif</p>
<div class="cta">
@if(!empty($whatsappUrl))<a class="btn btn-wa" href="{{ $whatsappUrl }}" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp {{ $config->whatsapp ?? $config->telefono ?? '' }}</a>@endif
<a class="btn btn-p" href="#seguimiento"><i class="fa-solid fa-magnifying-glass"></i> Consultar mi reparación</a>
@if(!empty($instagramUrl))<a class="btn btn-g" href="{{ $instagramUrl }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>@endif
@if(!empty($facebookUrl))<a class="btn btn-g" href="{{ $facebookUrl }}" target="_blank"><i class="fa-brands fa-facebook"></i></a>@endif
@if(!empty($tiktokUrl))<a class="btn btn-g" href="{{ $tiktokUrl }}" target="_blank"><i class="fa-brands fa-tiktok"></i></a>@endif
</div></div></section>
<section class="sec"><div class="wrap"><div class="card">
<h3 style="margin:0 0 4px"><i class="fa-solid fa-screwdriver-wrench" style="color:var(--cyan)"></i> Nuestros servicios</h3>
<p style="color:var(--muted);font-size:13px;margin:0 0 16px">Reparación profesional con garantía</p>
<div class="grid4">
<div class="srv"><div class="ic">📱</div><b>Cambio de pantalla</b><br><small>Todas las marcas · mismo día</small></div>
<div class="srv"><div class="ic">🔋</div><b>Batería</b><br><small>Originales con garantía</small></div>
<div class="srv"><div class="ic">💦</div><b>Placa / Agua</b><br><small>Diagnóstico gratis</small></div>
<div class="srv"><div class="ic">🔓</div><b>Liberación</b><br><small>Todas las operadoras</small></div>
</div>
@if(!empty($whatsappUrl))<a class="btn btn-p" style="width:100%;justify-content:center;margin-top:16px" href="{{ $whatsappUrl }}" target="_blank"><i class="fa-brands fa-whatsapp"></i> Cotizar mi reparación por WhatsApp</a>@endif
</div></div></section>
@if(!empty($productos) && $productos->count() > 0)
<section class="sec" style="padding-top:0"><div class="wrap"><div class="card">
<h3 style="margin:0 0 4px"><i class="fa-solid fa-bag-shopping" style="color:var(--cyan)"></i> Catálogo</h3>
<p style="color:var(--muted);font-size:13px;margin:0 0 16px">Stock real de la tienda · pide por WhatsApp</p>
<div class="grid4">
@foreach($productos as $prod)
<div class="prod"><b style="font-size:14px">{{ $prod->nombre }}</b>
<span class="price">{{ $config->simbolo_moneda ?? 'S/' }} {{ number_format($prod->precio_venta, 2) }}</span>
<span class="stock">● En stock ({{ $prod->stock }})</span>
@if(!empty($whatsappUrl))<a class="btn btn-wa btn-sm" style="justify-content:center;margin-top:6px;font-size:13px" target="_blank" href="https://wa.me/{{ preg_replace('/\D/', '', (string)($config->whatsapp ?? $config->telefono ?? '')) }}?text={{ urlencode('Hola, me interesa: ' . $prod->nombre . ' ¿Sigue disponible?') }}"><i class="fa-brands fa-whatsapp"></i> Pedir</a>@endif
</div>
@endforeach
</div></div></div></section>
@endif
<section class="sec" id="seguimiento" style="padding-top:0"><div class="wrap"><div class="card" style="text-align:center;background:linear-gradient(135deg,rgba(6,182,212,.12),rgba(59,130,246,.10)),var(--card)">
<h3 style="margin:0"><i class="fa-solid fa-magnifying-glass" style="color:var(--cyan)"></i> ¿Dejaste tu equipo en reparación?</h3>
<p style="color:var(--muted);font-size:13.5px">Ingresa tu código de boleta · ej: RPT-000002</p>
<form method="GET" action="{{ route('buscar.orden') }}" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
<input class="in" type="text" name="codigo" placeholder="RPT-000002" required>
<button class="btn btn-p" type="submit">Consultar estado</button>
</form>
<p style="color:var(--muted);font-size:12px">O escanea el QR de tu boleta 📷</p>
</div></div></section>
<section class="sec" style="padding-top:0"><div class="wrap"><div class="grid2">
<div>@if($cupones->isNotEmpty())@foreach($cupones as $cupon)<div class="cupon" style="margin-bottom:12px"><small style="color:var(--muted)">CUPÓN ACTIVO</small><h3 style="margin:4px 0">{{ $cupon->codigo }}</h3><b style="color:var(--emerald)">{{ $cupon->valor }}% de descuento</b><br><small style="color:var(--muted)">{{ $cupon->descripcion }}</small></div>@endforeach@endif</div>
<div class="card"><b><span class="stars">★★★★★</span> @if(isset($promedio) && $promedio){{ number_format($promedio,1) }} · @endifLo que dicen los clientes</b>
@foreach($resenas->take(4) as $r)<p style="font-size:13.5px;color:#e2e8f0;margin:10px 0"><span class="stars">{{ str_repeat('★', (int)$r->calificacion) }}</span> <b>{{ $r->nombre_cliente }}</b><br><span style="color:var(--muted)">{{ $r->comentario }}</span></p>@endforeach
<a class="btn btn-g" style="width:100%;justify-content:center" href="{{ route('public.resena.form', $tenant->slug_publico) }}"><i class="fa-solid fa-star"></i> Dejar mi reseña</a>
</div></div></div></section>
<section class="sec" style="padding-top:0"><div class="wrap"><div class="card" style="text-align:center">
<h3 style="margin:0"><i class="fa-solid fa-location-dot" style="color:var(--cyan)"></i> Visítanos</h3>
<p style="color:var(--muted);font-size:13.5px">@if($config->direccion){{ $config->direccion }} · @endif @if($config->horario_atencion){{ $config->horario_atencion }} · @endif @if($config->telefono){{ $config->telefono }}@endif</p>
@if($config->mapa_url)<div style="position:relative"><iframe src="{{ $config->mapa_url }}" width="100%" height="230" style="border:0;border-radius:12px" loading="lazy"></iframe><a class="btn btn-p btn-sm" style="position:absolute;top:12px;right:12px" target="_blank" href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($config->direccion ?? '') . ' ' . ($config->nombre_tienda ?? '')) }}"><i class="fa-solid fa-route"></i> Cómo llegar</a></div>@endif
</div></div></section>
<footer class="foot"><div class="wrap">© {{ date('Y') }} {{ $config->nombre_tienda ?? $tenant->empresa }} · Potenciado por LUITECH<br><small>luitech.fun/t/{{ $tenant->slug_publico }}</small></div></footer>
@if(!empty($whatsappUrl))<a class="wa-float" href="{{ $whatsappUrl }}" target="_blank">💬</a>@endif
</body></html>
