<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PwaController extends Controller
{
    /**
     * Genera el manifest.json dinámico con el nombre y logo de la empresa.
     */
    public function manifest()
    {
        $empresa = Configuracion::empresa();
        $nombre = $empresa?->nombre_tienda ?? 'CRM Celulares';
        $logoUrl = $this->getLogoUrl($empresa);

        $manifest = [
            'name' => $nombre,
            'short_name' => mb_substr($nombre, 0, 12),
            'description' => 'Sistema de gestión para tienda de celulares - Reparaciones, Ventas e Inventario',
            'id' => '/',
            'start_url' => '/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'background_color' => '#f4f0fb',
            'theme_color' => '#1a0a3e',
            'orientation' => 'portrait',
            'scope' => '/',
            'lang' => 'es',
            'categories' => ['business', 'productivity'],
            'icons' => $this->getIcons($logoUrl),
            'shortcuts' => [
                [
                    'name' => 'Nueva Reparación',
                    'short_name' => 'Nueva RPT',
                    'url' => '/reparaciones/create',
                    'icons' => [['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'purpose' => 'any']],
                ],
                [
                    'name' => 'Nueva Venta',
                    'short_name' => 'Nueva VTA',
                    'url' => '/ventas/create',
                    'icons' => [['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'purpose' => 'any']],
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Genera el icono PWA con el logo de la empresa.
     */
    public function icon(Request $request, int $size)
    {
        $size = in_array($size, [192, 512]) ? $size : 192;
        $tenant = Configuracion::empresa();
        $logoPath = $this->getLogoPath($tenant);

        // Si hay logo de la empresa, generar icono con él
        if ($logoPath && file_exists($logoPath)) {
            $img = $this->createIconFromLogo($logoPath, $size);
            if ($img) {
                return response($img, 200, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        // Fallback: icono por defecto
        $default = public_path('icons/icon-' . $size . '.png');
        if (file_exists($default)) {
            return response(file_get_contents($default), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }

    /**
     * Obtiene la URL del logo de la empresa.
     */
    private function getLogoUrl($empresa): ?string
    {
        if (!$empresa || !$empresa->logo) {
            return null;
        }

        $logoPath = str_replace('storage/', '', $empresa->logo);
        $fullPath = storage_path('app/public/' . $logoPath);
        if (file_exists($fullPath)) {
            return route('storage.serve', ['path' => $logoPath]);
        }

        return null;
    }

    /**
     * Obtiene la ruta física del logo de la empresa.
     */
    private function getLogoPath($empresa): ?string
    {
        if (!$empresa || !$empresa->logo) {
            return null;
        }

        $logoPath = str_replace('storage/', '', $empresa->logo);
        $fullPath = storage_path('app/public/' . $logoPath);
        return file_exists($fullPath) ? $fullPath : null;
    }

    /**
     * Genera los iconos del manifest.
     */
    private function getIcons(?string $logoUrl): array
    {
        $icons = [];

        foreach ([192, 512] as $size) {
            $icons[] = [
                'src' => '/pwa/icon/' . $size,
                'sizes' => $size . 'x' . $size,
                'type' => 'image/png',
                'purpose' => 'any',
            ];
            $icons[] = [
                'src' => '/pwa/icon/' . $size,
                'sizes' => $size . 'x' . $size,
                'type' => 'image/png',
                'purpose' => 'maskable',
            ];
        }

        return $icons;
    }

    /**
     * Crea un icono PNG a partir del logo de la empresa.
     */
    private function createIconFromLogo(string $logoPath, int $size): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        // Cargar logo
        $info = getimagesize($logoPath);
        if (!$info) {
            return null;
        }

        switch ($info['mime']) {
            case 'image/png':
                $src = imagecreatefrompng($logoPath);
                break;
            case 'image/jpeg':
                $src = imagecreatefromjpeg($logoPath);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($logoPath);
                break;
            case 'image/gif':
                $src = imagecreatefromgif($logoPath);
                break;
            default:
                return null;
        }

        if (!$src) {
            return null;
        }

        // Crear lienzo con fondo
        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        // Fondo oscuro (color de la marca)
        $bgDark = imagecolorallocate($img, 26, 10, 62);
        imagefilledrectangle($img, 0, 0, $size, $size, $bgDark);

        // Redimensionar logo al 70% del tamaño del icono
        $logoW = imagesx($src);
        $logoH = imagesy($src);
        $targetSize = (int)($size * 0.7);
        $ratio = min($targetSize / $logoW, $targetSize / $logoH);
        $newW = (int)($logoW * $ratio);
        $newH = (int)($logoH * $ratio);
        $dstX = (int)(($size - $newW) / 2);
        $dstY = (int)(($size - $newH) / 2);

        // Redimensionar con calidad
        $resized = imagecreatetruecolor($newW, $newH);
        imagesavealpha($resized, true);
        imagealphablending($resized, false);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $logoW, $logoH);

        // Pegar logo centrado
        imagecopy($img, $resized, $dstX, $dstY, 0, 0, $newW, $newH);

        // Guardar en buffer
        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();

        imagedestroy($img);
        imagedestroy($resized);
        imagedestroy($src);

        return $pngData;
    }
}