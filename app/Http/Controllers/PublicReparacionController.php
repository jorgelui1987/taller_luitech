<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\Tenant;
use App\Models\Configuracion;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;

class PublicReparacionController extends Controller
{
    /**
     * Vista pública para que el cliente escanee el QR
     * y vea el estado de su reparación, condiciones y garantía.
     * Sin código de orden muestra el portal de búsqueda (Consulta Express).
     */
    public function status($numero_orden = null)
    {
        // Si viene por query string (búsqueda desde el portal público)
        if (!$numero_orden && request()->filled('numero_orden')) {
            $numero_orden = request('numero_orden');
        }

        // Sin código: mostrar el portal de búsqueda en lugar de un 404
        if (!$numero_orden) {
            return view('public.estado-search');
        }

        // Normalizar y buscar (acepta "1024", "RPT-001024", "rpt001024", etc.)
        $valorOriginal = strtoupper(trim($numero_orden));
        $candidatos = array_values(array_unique([
            $this->normalizarNumeroOrden($valorOriginal),
            $valorOriginal,
        ]));

        $reparacion = Reparacion::withoutGlobalScopes()
            ->whereIn('numero_orden', $candidatos)
            ->first();

        // No encontrada: portal de búsqueda con mensaje amigable
        if (!$reparacion) {
            return view('public.estado-search', [
                'error'   => 'La orden ' . $candidatos[0] . ' no fue encontrada. Verifica el código de tu boleta e intenta nuevamente.',
                'buscado' => request('numero_orden', $candidatos[0]),
            ]);
        }

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

    /**
     * Normaliza el código de orden ingresado: "1024" → "RPT-001024".
     */
    private function normalizarNumeroOrden(string $valor): string
    {
        $valor = strtoupper(trim($valor));
        $digitos = preg_replace('/\D/', '', $valor);

        if ($digitos !== '') {
            return 'RPT-' . str_pad($digitos, 6, '0', STR_PAD_LEFT);
        }

        return $valor;
    }

    /**
     * Modo Sala de Espera (TV): pantalla completa con los turnos del taller.
     * Con slug (/pantalla/mitienda) muestra SOLO esa empresa; sin slug,
     * usa el tenant con actividad más reciente (instancia de una sola empresa).
     */
    public function pantalla(Request $request, ?string $slug = null)
    {
        $consejos = [
            ['titulo' => 'Cuida tu batería', 'desc' => 'Evita que tu celular se descargue por debajo del 20% o se cargue por encima del 80% de forma habitual: extenderás la vida útil de tu batería.'],
            ['titulo' => 'La limpieza salva vidas', 'desc' => 'Los notebooks acumulan polvo en sus ventiladores. Un mantenimiento térmico cada 12 meses evita fallas graves en procesador y gráfica.'],
            ['titulo' => 'Respalda tus archivos', 'desc' => 'Ningún disco duro es eterno. Mantén una copia de seguridad de tus fotos y documentos en la nube o en un disco externo.'],
            ['titulo' => 'Pantallas protegidas', 'desc' => 'El vidrio templado o hidrogel absorbe gran parte del impacto en caídas directas. Pregunta por el tuyo en el mesón.'],
            ['titulo' => 'Cargadores certificados', 'desc' => 'Los cargadores de baja calidad entregan voltajes inestables que dañan el puerto de carga y la placa de tu equipo.'],
            ['titulo' => 'Humedad: actúa rápido', 'desc' => 'Si tu equipo se moja, apágalo de inmediato y no intentes cargarlo. Tráelo cuanto antes: el tiempo es clave para salvar la placa.'],
        ];

        $tenantId = $slug !== null
            ? $this->resolverTenantPorSlug($slug)
            : $this->resolverTenantPantalla($request);

        $empresa = $tenantId
            ? Configuracion::withoutGlobalScopes()->where('tenant_id', $tenantId)->first()
            : null;

        return view('public.pantalla', [
            'consejos'        => $consejos,
            'empresaPantalla' => $empresa,
            'slugPantalla'    => $slug,
        ]);
    }

    /**
     * Datos en vivo del modo TV (consultado por la pantalla cada 15 s).
     * Con slug: solo esa empresa. Sin slug: fallback por actividad reciente.
     */
    public function pantallaData(Request $request, ?string $slug = null)
    {
        $tenantId = $slug !== null
            ? $this->resolverTenantPorSlug($slug)
            : $this->resolverTenantPantalla($request);

        $estadosActivos = ['recibido', 'en_diagnostico', 'esperando_repuesto', 'en_reparacion', 'listo'];

        $query = Reparacion::withoutGlobalScopes()
            ->whereIn('estado', $estadosActivos)
            ->orderByDesc('updated_at');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $ordenes = $query->limit(60)->get();

        $avances = [
            'recibido' => 10, 'en_diagnostico' => 30, 'esperando_repuesto' => 45,
            'en_reparacion' => 65, 'listo' => 85,
        ];
        $labels = [
            'recibido' => 'Recibido', 'en_diagnostico' => 'En diagnóstico',
            'esperando_repuesto' => 'Esperando repuesto', 'en_reparacion' => 'En reparación',
        ];

        $listos = [];
        $proceso = [];

        foreach ($ordenes as $r) {
            $equipo = trim((($r->marca ?? '') . ' ' . ($r->modelo ?? ''))) ?: ($r->dispositivo ?: 'Equipo en servicio');

            $item = [
                'codigo'     => $r->numero_orden,
                'equipo'     => $equipo,
                'urgente'    => in_array($r->prioridad, ['alta', 'urgente']),
                'avance'     => $avances[$r->estado] ?? 50,
                'estado_key' => $r->estado,
            ];

            if ($r->estado === 'listo') {
                $item['desde'] = optional($r->updated_at)->format('H:i');
                $listos[] = $item;
            } else {
                $item['estado'] = $labels[$r->estado] ?? ucfirst($r->estado);
                $proceso[] = $item;
            }
        }

        $empresa = $tenantId
            ? Configuracion::withoutGlobalScopes()->where('tenant_id', $tenantId)->first()
            : null;

        return response()->json([
            'tienda' => [
                'nombre'    => $empresa->nombre_tienda ?? 'Luitech Servicio Técnico',
                'direccion' => $empresa->direccion ?? '',
                'telefono'  => $empresa->telefono ?? '',
            ],
            'listos'    => $listos,
            'proceso'   => $proceso,
            'counts'    => ['listos' => count($listos), 'proceso' => count($proceso)],
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Resuelve el tenant para la pantalla de sala de espera.
     * Prioriza el parámetro ?tienda=; si no viene, usa el tenant con
     * actividad más reciente entre las órdenes activas (monotienda).
     */
    private function resolverTenantPantalla(Request $request): ?int
    {
        $param = $request->query('tienda');
        if ($param !== null && ctype_digit((string) $param) && (int) $param > 0) {
            return (int) $param;
        }

        $tenantId = Reparacion::withoutGlobalScopes()
            ->whereIn('estado', ['recibido', 'en_diagnostico', 'esperando_repuesto', 'en_reparacion', 'listo'])
            ->orderByDesc('updated_at')
            ->value('tenant_id');

        return $tenantId !== null ? (int) $tenantId : null;
    }

    /**
     * Resuelve el tenant por slug público de la tienda (/pantalla/mitienda).
     */
    private function resolverTenantPorSlug(string $slug): ?int
    {
        $tenant = Tenant::where('slug_publico', $slug)->first();

        if (!$tenant || $tenant->estado !== 'activo') {
            abort(404);
        }

        return (int) $tenant->id;
    }
}
