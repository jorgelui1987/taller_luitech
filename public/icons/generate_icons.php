<?php
// Script para generar iconos PWA a partir del SVG con GD
// Ejecutar: php generate_icons.php

$sizes = [192, 512];

foreach ($sizes as $size) {
    $img = imagecreatetruecolor($size, $size);

    // Fondo blanco transparente
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    // Colores
    $bgDark = imagecolorallocate($img, 26, 10, 62);       // #1a0a3e
    $purple = imagecolorallocate($img, 168, 85, 247);     // #a855f7
    $pink   = imagecolorallocate($img, 236, 72, 153);     // #ec4899
    $white  = imagecolorallocate($img, 255, 255, 255);
    $whiteAlpha = imagecolorallocatealpha($img, 255, 255, 255, 40);
    $green  = imagecolorallocate($img, 74, 222, 128);     // #4ade80

    // Escala
    $s = $size / 512;

    // Fondo redondeado
    $radius = (int)(96 * $s);
    $x0 = 0; $y0 = 0;
    $x1 = $size; $y1 = $size;

    // Dibujar rectángulo redondeado
    imagefilledrectangle($img, $x0 + $radius, $y0, $x1 - $radius, $y1, $bgDark);
    imagefilledrectangle($img, $x0, $y0 + $radius, $x1, $y1 - $radius, $bgDark);
    imagefilledellipse($img, $x0 + $radius, $y0 + $radius, $radius * 2, $radius * 2, $bgDark);
    imagefilledellipse($img, $x1 - $radius, $y0 + $radius, $radius * 2, $radius * 2, $bgDark);
    imagefilledellipse($img, $x0 + $radius, $y1 - $radius, $radius * 2, $radius * 2, $bgDark);
    imagefilledellipse($img, $x1 - $radius, $y1 - $radius, $radius * 2, $radius * 2, $bgDark);

    // Smartphone body
    $px = (int)(176 * $s); $py = (int)(96 * $s);
    $pw = (int)(160 * $s); $ph = (int)(320 * $s);
    $phoneRadius = (int)(24 * $s);

    // Gradiente manual para el cuerpo del teléfono
    $steps = max(8, (int)($pw / 4));
    for ($i = 0; $i < $steps; $i++) {
        $t = $i / max(1, $steps - 1);
        $r = (int)(168 + ($purple_r = 0) * $t);
        // Mezclar purple → pink
        $r = (int)(168 + (236 - 168) * $t);
        $g = (int)(85 + (72 - 85) * $t);
        $b = (int)(247 + (153 - 247) * $t);
        $gradColor = imagecolorallocate($img, $r, $g, $b);
        $xStart = $px + (int)($i * ($pw / $steps));
        $xEnd = $px + (int)(($i + 1) * ($pw / $steps));
        imagefilledrectangle($img, $xStart, $py, $xEnd, $py + $ph, $gradColor);
    }

    // Pantalla interior
    $sx = (int)(196 * $s); $sy = (int)(136 * $s);
    $sw = (int)(120 * $s); $sh = (int)(240 * $s);
    $screenRadius = (int)(8 * $s);
    imagefilledrectangle($img, $sx + $screenRadius, $sy, $sx + $sw - $screenRadius, $sy + $sh, $whiteAlpha);
    imagefilledrectangle($img, $sx, $sy + $screenRadius, $sx + $sw, $sy + $sh - $screenRadius, $whiteAlpha);
    imagefilledellipse($img, $sx + $screenRadius, $sy + $screenRadius, $screenRadius * 2, $screenRadius * 2, $whiteAlpha);
    imagefilledellipse($img, $sx + $sw - $screenRadius, $sy + $screenRadius, $screenRadius * 2, $screenRadius * 2, $whiteAlpha);
    imagefilledellipse($img, $sx + $screenRadius, $sy + $sh - $screenRadius, $screenRadius * 2, $screenRadius * 2, $whiteAlpha);
    imagefilledellipse($img, $sx + $sw - $screenRadius, $sy + $sh - $screenRadius, $screenRadius * 2, $screenRadius * 2, $whiteAlpha);

    // Círculo blanco grande (globo)
    $cx = (int)(256 * $s); $cy = (int)(240 * $s); $cr = (int)(40 * $s);
    imagefilledellipse($img, $cx, $cy, $cr * 2, $cr * 2, $white);

    // Fondo del globo
    $gx = (int)(256 * $s); $gy = (int)(240 * $s); $gr = (int)(30 * $s);
    imagefilledellipse($img, $gx, $gy, $gr * 2, $gr * 2, $purple);

    // Punto del globo
    $dot_x = (int)(256 * $s); $dot_y = (int)(270 * $s); $dot_r = (int)(8 * $s);
    imagefilledellipse($img, $dot_x, $dot_y, $dot_r * 2, $dot_r * 2, $pink);

    // Líneas de señal
    $lineWidth = (int)(6 * $s);
    imageline($img, (int)(216*$s), (int)(200*$s), (int)(208*$s), (int)(192*$s), $whiteAlpha);
    imageline($img, (int)(256*$s), (int)(180*$s), (int)(256*$s), (int)(168*$s), $whiteAlpha);
    imageline($img, (int)(296*$s), (int)(200*$s), (int)(304*$s), (int)(192*$s), $whiteAlpha);

    // Botón home
    $hx = (int)(256 * $s); $hy = (int)(380 * $s); $hr = (int)(8 * $s);
    imagefilledellipse($img, $hx, $hy, $hr * 2, $hr * 2, $whiteAlpha);

    // Aro verde (herramienta)
    $ringR = (int)(22 * $s);
    $ringCx = (int)(256 * $s); $ringCy = (int)(328 * $s);
    imageellipse($img, $ringCx, $ringCy, $ringR * 2, $ringR * 2, $green);

    // Línea de la herramienta
    $lineW = (int)(8 * $s);
    imageline($img, (int)(256*$s), (int)(326*$s), (int)(256*$s), (int)(346*$s), $green);

    // Guardar PNG
    $file = __DIR__ . "/icon-{$size}.png";
    imagepng($img, $file);
    imagedestroy($img);
    echo "✓ Generado: {$file}\n";
}

echo "¡Iconos generados correctamente!\n";
