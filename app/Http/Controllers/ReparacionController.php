<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\ReparacionFoto;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Venta;
use App\Models\Cupon;
use App\Helpers\WhatsAppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReparacionController extends Controller
{
    private const DATA_IMAGE_PNG_BASE64 = 'data:image/png;base64,';
    private const DIR_FIRMAS = 'firmas/';
    private const NOMBRE_TIENDA_DEFAULT = 'CRM Celulares';
    private const REGEX_SOLO_DIGITOS = '/\D/';
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
        $clientes  = Cliente::where('activo', true)
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->limit(50)
            ->get();
        $tecnicos  = User::where('tenant_id', Auth::user()->tenant_id)
            ->where('rol', 'tecnico')
            ->where('activo', true)
            ->orderBy('name')
            ->get();
        return view('reparaciones.create', compact('clientes', 'tecnicos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id'          => 'required|exists:clientes,id',
            'tecnico_id'          => 'required|exists:users,id',
            'tipo_dispositivo'    => 'required|in:celular,tablet,portatil,otros',
            'dispositivo'         => 'nullable|string|max:150',
            'codigo_equipo'       => 'nullable|string|max:80',
            'tipo_codigo'         => 'nullable|in:patron,pin',
            'patron_secuencia'    => 'nullable|string|max:50',
            'marca'               => 'nullable|string|max:80',
            'modelo'              => 'nullable|string|max:100',
            'imei'                => 'nullable|string|max:20',
            'color'               => 'nullable|string|max:50',
            'falla_reportada'     => 'required|string',
            'presupuesto'         => 'nullable|numeric|min:0',
            'abono'               => 'nullable|numeric|min:0',
            'costo_repuesto'      => 'nullable|numeric|min:0',
            'prioridad'           => 'required|in:baja,media,alta,urgente',
            'fecha_estimada'      => 'nullable|date',
            'notas'               => 'nullable|string',
            'firma_recepcion_data'=> 'nullable|string',
            'fotos.*'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'fotos_tipos.*'       => 'nullable|string',
            'cupon_codigo'        => 'nullable|string|max:30',
        ]);

        $validated['numero_orden']    = Reparacion::generarNumero();
        $validated['estado']          = 'recibido';
        $validated['fecha_recepcion'] = now();
        $validated['total']           = max(0, ($validated['presupuesto'] ?? 0) - ($validated['abono'] ?? 0));
        $validated['tenant_id']       = Auth::user()?->tenant_id;

        // ── Procesar cupón de descuento ──
        $cuponAplicado = null;
        if ($request->filled('cupon_codigo')) {
            $codigoCupon = strtoupper(trim($request->cupon_codigo));
            $cupon = Cupon::where('codigo', $codigoCupon)->first();

            if (!$cupon) {
                return back()->with('error', "El cupón '{$codigoCupon}' no existe.")
                    ->withInput();
            }

            if (!$cupon->esValido()) {
                return back()->with('error', "El cupón '{$codigoCupon}' ya fue usado o está expirado.")
                    ->withInput();
            }

            // Aplicar descuento según tipo
            if ($cupon->tipo === 'porcentaje') {
                $descuentoCupon = round($validated['total'] * ((float)$cupon->valor / 100), 2);
            } else {
                $descuentoCupon = (float)$cupon->valor;
            }

            $validated['total'] = max(0, $validated['total'] - $descuentoCupon);
            $cuponAplicado = $cupon;
        }

        // Guardar firma de recepción si fue dibujada
        if (!empty($request->firma_recepcion_data)) {
            $firmaData = str_replace(self::DATA_IMAGE_PNG_BASE64, '', $request->firma_recepcion_data);
            $firmaData = str_replace(' ', '+', $firmaData);
            $firmaData = base64_decode($firmaData);
            if ($firmaData !== false) {
                $nombreArchivo = 'firma_recepcion_' . Str::random(12) . '.png';
                $rutaFirma = self::DIR_FIRMAS . $nombreArchivo;
                Storage::disk('public')->put($rutaFirma, $firmaData);
                $validated['firma_recepcion'] = $rutaFirma;
            }
        }

        unset($validated['firma_recepcion_data'], $validated['fotos'], $validated['fotos_tipos']);

        $reparacion = Reparacion::create($validated);

        // ── MARCAR CUPÓN COMO USADO EN LA REPARACIÓN ──
        if ($cuponAplicado) {
            $cuponAplicado->marcarUsadoEnReparacion($reparacion->id);
        }

        // Guardar fotos de evidencia recibidas en la nueva orden
        if ($request->hasFile('fotos')) {
            $fotos = $request->file('fotos');
            $tipos = $request->input('fotos_tipos', []);
            foreach ($fotos as $index => $fotoFile) {
                if ($fotoFile && $fotoFile->isValid()) {
                    $tipo = $tipos[$index] ?? 'general';
                    $nombreArchivo = 'foto_' . $reparacion->id . '_' . Str::random(10) . '.' . $fotoFile->extension();
                    $rutaFoto = $fotoFile->storeAs('reparaciones/fotos', $nombreArchivo, 'public');
                    ReparacionFoto::create([
                        'reparacion_id' => $reparacion->id,
                        'ruta'          => $rutaFoto,
                        'tipo'          => $tipo,
                    ]);
                }
            }
        }

        // Cargar datos necesarios para la notificación
        $reparacion->load('cliente');

        // Obtener nombre de la tienda
        $empresa = Configuracion::empresa();
        $nombreTienda = $empresa?->nombre_tienda ?? self::NOMBRE_TIENDA_DEFAULT;

        // Generar URL de WhatsApp para notificar al cliente
        $whatsappUrl = null;
        $cliente = $reparacion->cliente;
        if ($cliente) {
            $telefono = $cliente->telefono ?? $cliente->celular;
            if ($telefono) {
                $urlEstado = route('reparaciones.public-status', $reparacion->numero_orden);
                $whatsappUrl = WhatsAppHelper::generarUrl(
                    $telefono,
                    WhatsAppHelper::mensajeRecibido($reparacion, $nombreTienda, $urlEstado)
                );
            }
        }

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

        // Obtener el cupón activo de esta reparación (si existe)
        $cupon = Cupon::withoutGlobalScopes()
            ->where('reparacion_id', $reparacion->id)
            ->where('estado', 'activo')
            ->where(function ($q) {
                $q->whereNull('fecha_expiracion')->orWhere('fecha_expiracion', '>', now());
            })
            ->first();

        // Obtener URL de la mini página web
        $tenant = $reparacion->tenant;
        $urlMiniWeb = $tenant?->slug_publico ? url('/t/' . $tenant->slug_publico) : null;

        return view('reparaciones.ticket', compact('reparacion', 'cupon', 'urlMiniWeb'));
    }

    /**
     * Genera el ticket de reparación como texto formateado y abre WhatsApp.
     */
    public function enviarWhatsApp(Reparacion $reparacion)
    {
        $reparacion->load(['cliente', 'tecnico']);
        $empresa = Configuracion::empresa();

        // Obtener teléfono del cliente
        $telefono = $reparacion->cliente?->telefono ?? $reparacion->cliente?->celular ?? null;
        if (!$telefono) {
            return back()->with('error', 'El cliente no tiene teléfono registrado para enviar por WhatsApp.');
        }

        // Limpiar teléfono: solo dígitos
        $telefono = preg_replace(self::REGEX_SOLO_DIGITOS, '', $telefono);

        // Si no tiene código de país, agregar el de la configuración
        $pais = $empresa?->pais ?? 'PE';
        $codigos = ['PE' => '51', 'CL' => '56', 'AR' => '54', 'MX' => '52', 'CO' => '57', 'EC' => '593', 'BO' => '591', 'US' => '1'];
        $codigoPais = $codigos[$pais] ?? '51';

        if (strlen($telefono) <= 9) {
            $telefono = $codigoPais . $telefono;
        }

        // Líneas del ticket
        $linea = str_repeat('─', 32);
        $lineaDoble = str_repeat('═', 32);

        $tipoDispositivo = ['celular' => 'Celular', 'tablet' => 'Tablet', 'portatil' => 'Portatil', 'otros' => 'Otros'];
        $estadoLabel = str_replace('_', ' ', ucfirst($reparacion->estado));

        $texto = $lineaDoble . "\n";
        $texto .= str_pad($empresa?->nombre_tienda ?? 'CRM Celulares', 32, ' ', STR_PAD_BOTH) . "\n";
        if ($empresa?->ruc) {
            $texto .= str_pad('RUC: ' . $empresa->ruc, 32, ' ', STR_PAD_BOTH) . "\n";
        }
        if ($empresa?->direccion) {
            $texto .= str_pad($empresa->direccion, 32, ' ', STR_PAD_BOTH) . "\n";
        }
        $texto .= $lineaDoble . "\n";
        $texto .= str_pad($reparacion->numero_orden, 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= str_pad($estadoLabel, 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= $linea . "\n";

        // Cliente
        $texto .= 'CLIENTE: ' . ($reparacion->cliente->nombre_completo ?? '—') . "\n";
        if ($reparacion->cliente?->telefono) {
            $texto .= 'TEL: ' . $reparacion->cliente->telefono . "\n";
        }
        $texto .= 'TECNICO: ' . ($reparacion->tecnico->name ?? '—') . "\n";
        $texto .= $linea . "\n";

        // Equipo
        $texto .= 'TIPO: ' . ($tipoDispositivo[$reparacion->tipo_dispositivo] ?? $reparacion->tipo_dispositivo ?? '—') . "\n";
        if ($reparacion->marca) {
            $texto .= 'MARCA: ' . $reparacion->marca . "\n";
        }
        if ($reparacion->modelo) {
            $texto .= 'MODELO: ' . $reparacion->modelo . "\n";
        }
        if ($reparacion->imei) {
            $texto .= 'IMEI: ' . $reparacion->imei . "\n";
        }
        if ($reparacion->color) {
            $texto .= 'COLOR: ' . $reparacion->color . "\n";
        }
        if ($reparacion->fecha_recepcion) {
            $texto .= 'RECIBIDO: ' . $reparacion->fecha_recepcion->format('d/m/Y H:i') . "\n";
        }
        if ($reparacion->fecha_estimada) {
            $texto .= 'EST. ENTREGA: ' . $reparacion->fecha_estimada->format('d/m/Y') . "\n";
        }
        $texto .= $linea . "\n";

        // Falla y diagnóstico
        if ($reparacion->falla_reportada) {
            $texto .= 'FALLA: ' . $reparacion->falla_reportada . "\n";
        }
        if ($reparacion->diagnostico) {
            $texto .= 'DIAGNOSTICO: ' . $reparacion->diagnostico . "\n";
        }
        if ($reparacion->solucion) {
            $texto .= 'SOLUCION: ' . $reparacion->solucion . "\n";
        }

        // Precios
        if ($reparacion->presupuesto > 0) {
            $texto .= 'PRESUPUESTO: S/ ' . number_format($reparacion->presupuesto, 2) . "\n";
        }
        if ($reparacion->costo_final > 0) {
            $texto .= 'COSTO FINAL: S/ ' . number_format($reparacion->costo_final, 2) . "\n";
        }
        if ($reparacion->abono > 0) {
            $texto .= 'ABONO: S/ ' . number_format($reparacion->abono, 2) . "\n";
        }
        if ($reparacion->total > 0) {
            $texto .= $lineaDoble . "\n";
            $texto .= 'TOTAL: S/ ' . number_format($reparacion->total, 2) . "\n";
            $texto .= $lineaDoble . "\n";
        }

        // Garantía
        if ($reparacion->garantia) {
            $texto .= "\nGarantía: " . $reparacion->dias_garantia . " días\n";
        }
        if ($empresa?->terminos_garantia) {
            $texto .= "\nGARANTÍA:\n" . $empresa->terminos_garantia . "\n";
        }

        // Notas
        if ($reparacion->notas) {
            $texto .= "\nNOTAS: " . $reparacion->notas . "\n";
        }

        $texto .= "\n" . str_pad('¡Gracias por su preferencia!', 32, ' ', STR_PAD_BOTH) . "\n";

        // Link para ver el estado en línea (lo que muestra el QR en la impresión)
        $urlEstado = route('reparaciones.public-status', $reparacion->numero_orden);
        $texto .= "\n🔗 *Sigue el estado de tu reparación:*\n" . $urlEstado . "\n";

        // URL de la mini página web
        $tenant = $reparacion->tenant;
        $urlMiniWeb = $tenant?->slug_publico ? url('/t/' . $tenant->slug_publico) : null;
        if ($urlMiniWeb) {
            $texto .= "\n🌐 *Visita nuestra tienda:*\n" . $urlMiniWeb . "\n";
        }

        // URL de WhatsApp
        $url = 'https://wa.me/' . $telefono . '?text=' . urlencode($texto);

        return redirect()->away($url);
    }

    public function edit(Reparacion $reparacion)
    {
        $clientes = Cliente::where('activo', true)->orderBy('nombre')->get();
        $tecnicos = User::where('tenant_id', Auth::user()->tenant_id)
            ->where('rol', 'tecnico')
            ->where('activo', true)
            ->orderBy('name')
            ->get();
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
            'metodo_pago'     => 'nullable|in:efectivo,tarjeta,transferencia,mercadopago',
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
            'costo_repuesto'  => 'nullable|numeric|min:0',
            'comision_porcentaje' => 'nullable|numeric|min:0|max:100',
            'estado'          => 'required|in:recibido,en_diagnostico,esperando_repuesto,en_reparacion,listo,entregado,no_reparable',
            'prioridad'       => 'required|in:baja,media,alta,urgente',
            'fecha_estimada'  => 'nullable|date',
            'garantia'        => 'boolean',
            'dias_garantia'   => 'nullable|integer|min:0',
            'notas'           => 'nullable|string',
            'cupon_codigo'    => 'nullable|string|max:30',
        ]);

        // Auto-calcular total = costo_final - abono si existe, de lo contrario presupuesto - abono
        $precioBase = ($validated['costo_final'] ?? 0) > 0
            ? (float) $validated['costo_final']
            : (float) ($validated['presupuesto'] ?? 0);
        $validated['total'] = max(0, $precioBase - (float) ($validated['abono'] ?? 0));

        // ── Procesar cupón de descuento ──
        $cuponAplicado = null;
        if ($request->filled('cupon_codigo')) {
            $codigoCupon = strtoupper(trim($request->cupon_codigo));
            $cupon = Cupon::where('codigo', $codigoCupon)->first();

            if (!$cupon) {
                return back()->with('error', "El cupón '{$codigoCupon}' no existe.")
                    ->withInput();
            }

            if (!$cupon->esValido()) {
                return back()->with('error', "El cupón '{$codigoCupon}' ya fue usado o está expirado.")
                    ->withInput();
            }

            // Evitar que el cupón se use en la misma reparación que lo generó
            if ($cupon->reparacion_id === $reparacion->id) {
                return back()->with('error', "El cupón '{$codigoCupon}' fue generado en esta misma reparación y no puede usarse aquí.")
                    ->withInput();
            }

            // Aplicar descuento según tipo
            if ($cupon->tipo === 'porcentaje') {
                $descuentoCupon = round($validated['total'] * ((float)$cupon->valor / 100), 2);
            } else {
                $descuentoCupon = (float)$cupon->valor;
            }

            $validated['total'] = max(0, $validated['total'] - $descuentoCupon);
            $cuponAplicado = $cupon;
        }

        // ── Si se está entregando, solo registrar fecha de entrega ──
        // (Sin autocompletar costo_final con presupuesto)
        if ($validated['estado'] === 'entregado') {
            if (!$reparacion->fecha_entrega) {
                $validated['fecha_entrega'] = now();
            }
        }

        // Verificar si la reparación ya fue entregada antes
        $yaEntregada = $reparacion->estado === 'entregado';

        // Guardar el estado anterior antes de actualizar
        $estadoAnterior = $reparacion->estado;

        // Procesar firma de entrega si viene desde el formulario
        if ($request->filled('firma_entrega_data')) {
            $firmaData = $request->firma_entrega_data;
            $firmaData = str_replace(self::DATA_IMAGE_PNG_BASE64, '', $firmaData);
            $firmaData = str_replace(' ', '+', $firmaData);
            $firmaData = base64_decode($firmaData);

            if ($firmaData !== false) {
                $nombreArchivo = 'firma_entrega_' . $reparacion->id . '_' . Str::random(8) . '.png';
                $ruta = self::DIR_FIRMAS . $nombreArchivo;

                Storage::disk('public')->put($ruta, $firmaData);

                // Eliminar firma anterior si existe
                if ($reparacion->firma_entrega) {
                    Storage::disk('public')->delete($reparacion->firma_entrega);
                }

                $reparacion->firma_entrega = $ruta;
            }
        }

        $reparacion->update($validated);

        // ── MARCAR CUPÓN COMO USADO EN LA REPARACIÓN ──
        if ($cuponAplicado) {
            $cuponAplicado->marcarUsadoEnReparacion($reparacion->id);
        }

        // ── CREAR VENTA AUTOMÁTICA Y COMISIÓN AL ENTREGAR REPARACIÓN ──
        if ($validated['estado'] === 'entregado') {
            // Generar cupón de descuento automático al entregar
            if (!$yaEntregada) {
                \App\Http\Controllers\ComboPublicidadController::generarCuponAlEntregar($reparacion);
            }

            // Si no se ingresó costo_final (0 o null), usar presupuesto
            $totalReparacion = (float)(($validated['costo_final'] ?? 0) > 0
                ? $validated['costo_final']
                : ($validated['presupuesto'] ?? $reparacion->presupuesto ?? $reparacion->total ?? 0));

            // Aplicar descuento del cupón al total de la venta automática
            if ($cuponAplicado) {
                if ($cuponAplicado->tipo === 'porcentaje') {
                    $descuentoVenta = round($totalReparacion * ((float)$cuponAplicado->valor / 100), 2);
                } else {
                    $descuentoVenta = (float)$cuponAplicado->valor;
                }
                $totalReparacion = max(0, $totalReparacion - $descuentoVenta);
            }

            // ── Calcular comisión del técnico ──
            // Fórmula: comisión = (Monto cobrado - costo_repuesto) × (% / 100)
            // El % puede venir del formulario (manual) o del perfil del técnico
            $tecnico = User::find($validated['tecnico_id'] ?? $reparacion->tecnico_id);
            $comisionPorcentaje = null;

            // Si se envió un % específico en el formulario, usarlo (Idea 2)
            if (array_key_exists('comision_porcentaje', $validated)
                && $validated['comision_porcentaje'] !== null
                && $validated['comision_porcentaje'] !== '') {
                $comisionPorcentaje = (float)$validated['comision_porcentaje'];
            } elseif ($tecnico && $tecnico->comision_porcentaje !== null && $tecnico->comision_porcentaje > 0) {
                // Si no se envió %, usar el % del perfil del técnico
                $comisionPorcentaje = (float)$tecnico->comision_porcentaje;
            }

            // Calcular base: usa costo_final si existe, si no presupuesto, menos repuesto (Idea 1)
            $reparacion->comision_porcentaje = $comisionPorcentaje;
            $baseComision = $reparacion->baseComision();

            $comisionMonto = 0;
            if ($comisionPorcentaje !== null && $baseComision > 0) {
                $comisionMonto = round($baseComision * ($comisionPorcentaje / 100), 2);
            }

            // Guardar o recalcular comisión si la comisión aún no ha sido pagada
            if (!$reparacion->comision_pagada && $comisionPorcentaje !== null) {
                $reparacion->update([
                    'comision_porcentaje' => $comisionPorcentaje,
                    'comision_monto'      => $comisionMonto,
                ]);
            }

            // Crear venta la primera vez que pasa a entregado
            if (!$yaEntregada && ($totalReparacion > 0 || $request->filled('cobrar_sin_costo'))) {
                $metodoPago = $request->metodo_pago ?? 'efectivo';

                Venta::create([
                    'numero_venta'   => Venta::generarNumero(),
                    'cliente_id'     => $reparacion->cliente_id,
                    'user_id'        => Auth::id(),
                    'fecha_venta'    => now(),
                    'subtotal'       => $totalReparacion,
                    'descuento'      => 0,
                    'impuesto'       => 0,
                    'total'          => $totalReparacion,
                    'comision_monto' => $comisionMonto,
                    'comision_pagada'=> false,
                    'metodo_pago'    => $metodoPago,
                    'estado'         => 'completada',
                    'notas'          => "Pago por reparación {$reparacion->numero_orden} - {$reparacion->dispositivo}",
                    'tenant_id'      => Auth::user()->tenant_id ?? $reparacion->tenant_id,
                ]);
            }
        }

        // Notificar por WhatsApp si cambió a "listo" o "entregado"
        $whatsappUrl = null;
        $nuevoEstado = $validated['estado'];

        if (in_array($nuevoEstado, ['listo', 'entregado']) && $nuevoEstado !== $estadoAnterior) {
            $reparacion->load('cliente');
            $empresa = Configuracion::empresa();
            $nombreTienda = $empresa?->nombre_tienda ?? 'CRM Celulares';
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

                // URL de la mini página web
                $tenant = $reparacion->tenant;
                $urlMiniWeb = $tenant?->slug_publico ? url('/t/' . $tenant->slug_publico) : null;
                if ($urlMiniWeb) {
                    $mensaje .= "\n\n🌐 *Visita nuestra tienda:*\n{$urlMiniWeb}";
                }
            }

            if ($cliente) {
                $telefono = $cliente->telefono ?? $cliente->celular;
                if ($telefono) {
                    $whatsappUrl = WhatsAppHelper::generarUrl($telefono, $mensaje);
                }
            }
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
        $firmaData = str_replace(self::DATA_IMAGE_PNG_BASE64, '', $firmaData);
        $firmaData = str_replace(' ', '+', $firmaData);
        $firmaData = base64_decode($firmaData);

        if ($firmaData === false) {
            return response()->json(['success' => false, 'message' => 'Error al decodificar la firma.'], 400);
        }

        $nombreArchivo = 'firma_' . $request->tipo . '_' . $reparacion->id . '_' . Str::random(8) . '.png';
        $ruta = self::DIR_FIRMAS . $nombreArchivo;

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
