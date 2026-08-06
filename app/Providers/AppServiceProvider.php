<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Configuracion;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Forzar HTTPS si APP_URL comienza con https://
        // (Dokploy maneja SSL externamente con Traefik, pero Laravel ve HTTP internamente)
        $appUrl = env('APP_URL', 'http://localhost');
        if (str_starts_with($appUrl, 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            \Illuminate\Support\Facades\URL::forceRootUrl($appUrl);
        } elseif ($this->app->environment('production') || env('FORCE_HTTPS', false) === true) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Compartir datos de la empresa con todas las vistas
        View::composer('*', function ($view) {
            // Solo agregar empresa si la vista no la tiene ya definida
            // (permite que controladores públicos pasen su propia $empresa)
            $viewData = $view->getData();
            if (array_key_exists('empresa', $viewData)) {
                return;
            }

            try {
                $empresa = Configuracion::empresa();
            } catch (\Exception $e) {
                $empresa = null;
            }

            // Si no hay configuración, crear un objeto con valores por defecto
            if (!$empresa) {
                $empresa = (object) [
                    'nombre_tienda'    => 'CRM Celulares',
                    'ruc'              => '',
                    'direccion'        => '',
                    'telefono'         => '',
                    'whatsapp'         => '',
                    'email'            => '',
                    'logo'             => null,
                    'igv'              => 18,
                    'moneda'           => 'PEN',
                    'simbolo_moneda'   => 'S/.',
                    'terminos_garantia' => '',
                ];
            }
            
            // Si hay logo, usar una ruta directa al archivo (sin symlink)
            if ($empresa && $empresa->logo) {
                $logoPath = str_replace('storage/', '', $empresa->logo);
                $fullPath = storage_path('app/public/' . $logoPath);
                if (file_exists($fullPath)) {
                    $empresa->logo_url = route('storage.serve', ['path' => $logoPath]);
                } else {
                    $empresa->logo_url = null;
                }
            } else {
                $empresa->logo_url = null;
            }

            $view->with('empresa', $empresa);
        });
    }
}
