<?php

namespace App\Helpers;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

class AuditoriaHelper
{
    /**
     * Registrar una acción de auditoría.
     */
    public static function registrar(
        string $accion,
        ?string $entidad = null,
        ?int $entidadId = null,
        ?string $detalle = null
    ): void {
        try {
            $user = Auth::user();

            Auditoria::create([
                'tenant_id'  => $user?->tenant_id,
                'user_id'    => $user?->id,
                'accion'     => $accion,
                'entidad'    => $entidad,
                'entidad_id' => $entidadId,
                'detalle'    => $detalle,
                'ip'         => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            // No bloquear la operación principal si falla la auditoría
            \Illuminate\Support\Facades\Log::warning('Error al registrar auditoría: ' . $e->getMessage());
        }
    }
}