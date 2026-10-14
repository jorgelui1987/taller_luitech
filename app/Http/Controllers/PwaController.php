<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PwaController extends Controller
{
    private const CONTENT_TYPE_PNG = 'image/png';
    private const ICON_SIZES = [192, 512];

    /**
     * Genera el manifest.json dinámico con el nombre y logo de la empresa.
     * Orden: 1) usuario logueado (su tienda), 2) ?t=slug (PWA instalada
     * desde una sesión de otra tienda), 3) primera empresa (fallback).
     */
    public function manifest(Request $request)
    {
        $empresa = $this->empresaParaPwa($request);
        $nombre = $empresa?->nombre_tienda ?? 'CRM Celulares';
        $slug = $empresa?->tenant?->slug_publico
            ?? $request->query('t')
            ?? optional(auth()->user()?->tenant)->slug_publico;

        $manifest = [
            'name' => $nombre,
            'short_name' => mb_substr($nombre, 0, 12),
            'description' => 'Sistema de gestión para tienda de celulares - Reparaciones, Ventas e Inventario',
            'id' => $slug ? ('/?t=' . $slug) : '/',
            'start_url' => $slug ? ('/?t=' . $slug) : '/',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'background_color' => '#f4f0fb',
            'theme_color' => '#1a0a3e',
            'orientation' => 'portrait',
            'lang' => 'es',
            'categories' => ['business', 'productivity'],
            'icons' => $this->getIcons($slug),
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
     * Genera el icono PWA con el logo de la empresa (misma resolución de tienda).
     */
    public function icon(Request $request, int $size)
    {
        $size = in_array($size, self::ICON_SIZES) ? $size : 192;
        $tenant = $this->empresaParaPwa($request);
        $logoPath = $this->getLogoPath($tenant);

        // Si hay logo de la empresa, generar icono con él
        if ($logoPath && file_exists($logoPath)) {
            $img = $this->createIconFromLogo($logoPath, $size);
            if ($img) {
                return $this->pngResponse($img);
            }
        }

        // Fallback: icono por defecto
        $default = public_path('icons/icon-' . $size . '.png');
        if (file_exists($default)) {
            return $this->pngResponse(file_get_contents($default));
        }

        abort(404);
    }

    private function pngResponse(string $data)
    {
        return response($data, 200, [
            'Content-Type' => self::CONTENT_TYPE_PNG,
            'Cache-Control' => 'public, max-age=86400',
        ]);
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
     * Genera los iconos del manifest (con ?t=slug para el logo de la tienda).
     */
    private function getIcons(?string $slug = null): array
    {
        $icons = [];
        $q = $slug ? ('?t=' . urlencode($slug)) : '';

        foreach (self::ICON_SIZES as $size) {
            $icons[] = [
                'src' => '/pwa/icon/' . $size . $q,
                'sizes' => $size . 'x' . $size,
                'type' => self::CONTENT_TYPE_PNG,
                'purpose' => 'any',
            ];
            $icons[] = [
                'src' => '/pwa/icon/' . $size . $q,
                'sizes' => $size . 'x' . $size,
                'type' => self::CONTENT_TYPE_PNG,
                'purpose' => 'maskable',
            ];
        }

        return $icons;
    }

    /**
     * Resuelve la configuración de la tienda para la PWA:
     * 1) usuario logueado → su tenant; 2) ?t=slug → esa tienda;
     * 3) fallback global (primera empresa).
     */
    private function empresaParaPwa(Request $request): ?Configuracion
    {
        if (auth()->check() && auth()->user()?->tenant_id) {
            $propia = Configuracion::where('tenant_id', auth()->user()->tenant_id)->first();
            if ($propia) return $propia;
        }

        $slug = trim((string) $request->query('t', ''));
        if ($slug !== '') {
            $tenant = \App\Models\Tenant::where('slug_publico', $slug)
                ->orWhere('subdominio', $slug)
                ->first();
            if ($tenant) {
                $deTienda = Configuracion::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)->first();
                if ($deTienda) return $deTienda;
            }
        }

        return Configuracion::empresa();
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

        $src = $this->loadImageSource($logoPath, $info['mime']);
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

    private function loadImageSource(string $logoPath, string $mime)
    {
        switch ($mime) {
            case self::CONTENT_TYPE_PNG:
                return imagecreatefrompng($logoPath);
            case 'image/jpeg':
                return imagecreatefromjpeg($logoPath);
            case 'image/webp':
                return imagecreatefromwebp($logoPath);
            case 'image/gif':
                return imagecreatefromgif($logoPath);
            default:
                return null;
        }
    }
}
