<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\Configuracion;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class PublicReparacionController extends Controller
{
    /**
     * Vista pública para que el cliente escanee el QR
     * y vea el estado de su reparación, condiciones y garantía.
     */
    public function status($numero_orden = null)
    {
        // Si viene por query string (búsqueda desde la mini web)
        if (!$numero_orden && request()->filled('numero_orden')) {
            $numero_orden = request('numero_orden');
        }

        $reparacion = Reparacion::withoutGlobalScopes()
            ->where('numero_orden', $numero_orden)
            ->firstOrFail();

        // Cargar relaciones SIN TenantScope para evitar que el scope
        // filtre por el tenant del usuario autenticado (que puede ser diferente
        // al tenant de la reparación cuando se accede desde el QR público)
        $reparacion->setRelation('cliente', Cliente::withoutGlobalScopes()->find($reparacion->cliente_id));
        $reparacion->setRelation('tecnico', User::withoutGlobalScopes()->find($reparacion->tecnico_id));

        // Obtener configuración del tenant SIN TenantScope
        $empresa = Configuracion::withoutGlobalScopes()
            ->where('tenant_id', $reparacion->tenant_id)
            ->first();

        // Si no hay configuración, crear un objeto con valores por defecto
        if (!$empresa) {
            $empresa = (object) [
                'nombre_tienda'     => 'CRM Celulares',
                'ruc'               => '',
                'direccion'         => '',
                'telefono'          => '',
                'email'             => '',
                'logo'              => null,
                'terminos_garantia' => '',
            ];
        }

        return view('reparaciones.public-status', compact('reparacion', 'empresa'));
    }
}