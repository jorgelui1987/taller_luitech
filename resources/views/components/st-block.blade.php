@props(['color' => '#0284c7', 'titulo' => ''])
<div class="st-block mb-3">
    <div class="st-head"><span class="st-dot" style="background:{{ $color }};"></span><h2>{{ $titulo }}</h2></div>
    {{ $slot }}
</div>
