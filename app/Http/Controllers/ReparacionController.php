<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\ReparacionFoto;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Configuracion;
use App\Helpers\WhatsAppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReparacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Reparacion::with(['cliente', 'tecnico']);

        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_orden', 'like', "%{$request->buscar}%")
                  ->orWhere('dispositivo', 'like', "%{$request->buscar}%")
                  ->orWhereHas('cliente', fn($cq) =>
                      $cq->where('nombre', 'like', "%{$request->buscar}%")
                         ->orWhere('apellido', 'like', "%{$request->buscar}%")
                  );
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        $reparaciones = $query->orderByDesc('fecha_recepcion')->paginate(15);
        $estadisticas = [
            'recibidos'   => Reparacion::where('estado', 'recibido')->count(),
            'en_proceso'  => Reparacion::whereIn('estado', ['en_diagnostico','en_reparacion','esperando_repuesto'])->count(),
            'listos'      => Reparacion::where('estado', 'listo')->count(),
            'entregados'  => Reparacion::where('estado', 'entregado')->count(),
        ];

        return view('reparaciones.index', compact('reparaciones', 'estadisticas'));
    }

    public function create()
    {
        $clientes  = Cliente::where('activo', true)->orderBy('nombre')->get();
        $tecnicos  = User::where('rol', 'tecnico')->where('activo', true)->orderBy('name')->get();
        return view('reparaciones.create', compact('clientes', 'tecnicos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'      => 'required|exists:clientes,id',
            'tecnico_id'      => 'required|exists:users,id',
            'tipo_dispositivo'=> 'required|in:celular,tablet,portatil,otros',
            'dispositivo'     => 'nullable|string|max:150',
            'codigo_equipo'   => 'nullable|string|max:80',
            'tipo_codigo'     => 'nullable|in:patron,pin',
            'patron_secuencia'=> 'nullable|string|max:50',
            'marca'           => 'nullable|string|max:80',
            'modelo'          => 'nullable|string|max:100',
            'imei'            => 'nullable|string|max:20',
            'color'           => 'nullable|string|max:50',
            'falla_reportada' => 'required|string',
            'presupuesto'     => 'nullable|numeric|min:0',
            'abono'           => 'nullable|numeric|min:0',
            'prioridad'       => 'required|in:baja,media,alta,urgente',
            'fecha_estimada'  => 'nullable|date',
            'notas'           => 'nullable|string',
        ]);

        $validated['numero_orden']    = Reparacion::generarNumero();
        $validated['estado']          = 'recibido';
        $validated['fecha_recepcion'] = now();
        $validated['total']           = max(0, ($validated['presupuesto'] ?? 0) - ($validated['abono'] ?? 0));
        $validated['tenant_id']       = Auth::user()->tenant_id;

        $reparacion = Reparacion::create($validated);

        // Cargar datos necesarios para la notificación
        $reparacion->load('cliente');

        // Obtener nombre de la tienda
        $empresa = Configuracion::empresa();
        $nombreTienda = $empresa->nombre_tienda ?? 'CRM Celulares';

        // Generar URL de WhatsApp para notificar al cliente
        $cliente = $reparacion->cliente;
        $urlEstado = route('reparaciones.public-status', $reparacion->numero_orden);
        $whatsappUrl = WhatsAppHelper::generarUrl(
            $cliente->telefono ?? $cliente->celular,
            WhatsAppHelper::mensajeRecibido($reparacion, $nombreTienda, $urlEstado)
        );

        $redirect = redirect()->route('reparaciones.show', $reparacion)
            ->with('success', 'Orden de reparación registrada correctamente.');

        if ($whatsappUrl) {
            $redirect->with('whatsapp_url', $whatsappUrl);
        }

        return $redirect;
    }

    public function show(Reparacion $reparacion)
    {
        $reparacion->load(['cliente', 'tecnico', 'fotos']);
        return view('reparaciones.show', compact('reparacion'));
    }

    public function printTicket(Reparacion $reparacion)
    {
        $reparacion->load(['cliente', 'tecnico']);
        return view('reparaciones.ticket', compact('reparacion'));
    }

    public function edit(Reparacion $reparacion)
    {
        $clientes = Cliente::where('activo', true)->orderBy('nombre')->get();
        $tecnicos = User::where('rol', 'tecnico')->where('activo', true)->orderBy('name')->get();
        return view('reparaciones.edit', compact('reparacion', 'clientes', 'tecnicos'));
    }

    public function destroy(Reparacion $reparacion)
    {
        // Solo admin puede eliminar
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'Solo el administrador puede eliminar órdenes de reparación.');
        }

        $reparacion->delete();

        return redirect()->route('reparaciones.index')
            ->with('success', 'Orden de reparación eliminada correctamente.');
    }

    public function update(Request $request, Reparacion $reparacion)
    {
        $validated = $request->validate([
            'tecnico_id'      => 'required|exists:users,id',
            'tipo_dispositivo'=> 'required|in:celular,tablet,portatil,otros',
            'dispositivo'     => 'nullable|string|max:150',
            'codigo_equipo'   => 'nullable|string|max:80',
            'tipo_codigo'     => 'nullable|in:patron,pin',
            'patron_secuencia'=> 'nullable|string|max:50',
            'marca'           => 'nullable|string|max:80',
            'modelo'          => 'nullable|string|max:100',
            'imei'            => 'nullable|string|max:20',
            'color'           => 'nullable|string|max:50',
            'falla_reportada' => 'required|string',
            'diagnostico'     => 'nullable|string',
            'solucion'        => 'nullable|string',
            'presupuesto'     => 'nullable|numeric|min:0',
            'abono'           => 'nullable|numeric|min:0',
            'costo_final'     => 'nullable|numeric|min:0',
            'estado'          => 'required|in:recibido,en_diagnostico,esperando_repuesto,en_reparacion,listo,entregado,no_reparable',
            'prioridad'       => 'required|in:baja,media,alta,urgente',
            'fecha_estimada'  => 'nullable|date',
            'garantia'        => 'boolean',
            'dias_garantia'   => 'nullable|integer|min:0',
            'notas'           => 'nullable|string',
        ]);

        // Auto-calcular total = presupuesto - abono
        $validated['total'] = max(0, ($validated['presupuesto'] ?? 0) - ($validated['abono'] ?? 0));

        if ($validated['estado'] === 'entregado' && !$reparacion->fecha_entrega) {
            $validated['fecha_entrega'] = now();
        }

        // Guardar el estado anterior antes de actualizar
        $estadoAnterior = $reparacion->estado;

        // Procesar firma de entrega si viene desde el formulario
        if ($request->filled('firma_entrega_data')) {
            $firmaData = $request->firma_entrega_data;
            $firmaData = str_replace('data:image/png;base64,', '', $firmaData);
            $firmaData = str_replace(' ', '+', $firmaData);
            $firmaData = base64_decode($firmaData);

            if ($firmaData !== false) {
                $nombreArchivo = 'firma_entrega_' . $reparacion->id . '_' . Str::random(8) . '.png';
                $ruta = 'firmas/' . $nombreArchivo;

                Storage::disk('public')->put($ruta, $firmaData);

                // Eliminar firma anterior si existe
                if ($reparacion->firma_entrega) {
                    Storage::disk('public')->delete($reparacion->firma_entrega);
                }

                $reparacion->firma_entrega = $ruta;
            }
        }

        $reparacion->update($validated);

        // Notificar por WhatsApp si cambió a "listo" o "entregado"
        $whatsappUrl = null;
        $nuevoEstado = $validated['estado'];

        if (in_array($nuevoEstado, ['listo', 'entregado']) && $nuevoEstado !== $estadoAnterior) {
            $reparacion->load('cliente');
            $empresa = Configuracion::empresa();
            $nombreTienda = $empresa->nombre_tienda ?? 'CRM Celulares';
            $cliente = $reparacion->cliente;
            $urlEstado = route('reparaciones.public-status', $reparacion->numero_orden);

            if ($nuevoEstado === 'listo') {
                $mensaje = WhatsAppHelper::mensajeListo($reparacion, $nombreTienda, $urlEstado);
            } else {
                $costo = number_format($reparacion->costo_final ?: $reparacion->presupuesto ?: 0, 2);
                $mensaje = "✅ *{$nombreTienda} - Orden de Reparación* - *ENTREGADO*\n\n" .
                    "📋 N° Orden: {$reparacion->numero_orden}\n" .
                    "📱 Equipo: {$reparacion->dispositivo} {$reparacion->marca} {$reparacion->modelo}\n" .
                    "✅ *¡Equipo entregado al cliente!*\n" .
                    "💰 Cobrado: S/ {$costo}\n" .
                    "📅 Entregado: " . now()->format('d/m/Y H:i') . "\n\n" .
                    "¡Gracias por su preferencia!";

                if ($urlEstado) {
                    $mensaje .= "\n\n🔗 *Estado en línea:*\n{$urlEstado}";
                }
            }

            $whatsappUrl = WhatsAppHelper::generarUrl(
                $cliente->telefono ?? $cliente->celular,
                $mensaje
            );
        }

        $redirect = redirect()->route('reparaciones.show', $reparacion)
            ->with('success', 'Reparación actualizada correctamente.');

        if ($whatsappUrl) {
            $redirect->with('whatsapp_url', $whatsappUrl);
        }

        return $redirect;
    }

    /**
     * Subir firma del cliente (vía AJAX)
     */
    public function subirFirma(Request $request, Reparacion $reparacion)
    {
        $request->validate([
            'firma' => 'required|string',
            'tipo'  => 'required|in:recepcion,entrega',
        ]);

        // Decodificar la imagen base64 (data:image/png;base64,...
        $firmaData = $request->firma;
        $firmaData = str_replace('data:image/png;base64,', '', $firmaData);
        $firmaData = str_replace(' ', '+', $firmaData);
        $firmaData = base64_decode($firmaData);

        if ($firmaData === false) {
            return response()->json(['success' => false, 'message' => 'Error al decodificar la firma.'], 400);
        }

        $nombreArchivo = 'firma_' . $request->tipo . '_' . $reparacion->id . '_' . Str::random(8) . '.png';
        $ruta = 'firmas/' . $nombreArchivo;

        Storage::disk('public')->put($ruta, $firmaData);

        // Eliminar firma anterior si existe
        $campo = $request->tipo === 'recepcion' ? 'firma_recepcion' : 'firma_entrega';
        if ($reparacion->{$campo}) {
            Storage::disk('public')->delete($reparacion->{$campo});
        }

        $reparacion->update([$campo => $ruta]);

        return response()->json([
            'success' => true,
            'url'     => asset('storage/' . $ruta),
            'message' => 'Firma guardada correctamente.',
        ]);
    }

    /**
     * Subir foto de evidencia (vía AJAX)
     */
    public function subirFoto(Request $request, Reparacion $reparacion)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'tipo' => 'required|in:frontal,trasero,detalle,imei,general',
        ]);

        $archivo = $request->file('foto');
        $nombreArchivo = 'foto_' . $reparacion->id . '_' . Str::random(12) . '.' . $archivo->extension();
        $ruta = $archivo->storeAs('reparaciones/fotos', $nombreArchivo, 'public');

        $foto = ReparacionFoto::create([
            'reparacion_id' => $reparacion->id,
            'ruta'          => $ruta,
            'tipo'          => $request->tipo,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $foto->id,
            'url'     => asset('storage/' . $ruta),
            'message' => 'Foto guardada correctamente.',
        ]);
    }

    /**
     * Eliminar foto de evidencia
     */
    public function eliminarFoto(ReparacionFoto $foto)
    {
        $reparacion = $foto->reparacion;

        // Verificar permisos
        if (Auth::user()->rol !== 'admin' && Auth::user()->id !== $reparacion->tecnico_id) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para eliminar esta foto.'], 403);
        }

        Storage::disk('public')->delete($foto->ruta);
        $foto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Foto eliminada correctamente.',
        ]);
    }
}
