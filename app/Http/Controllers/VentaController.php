<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\DetalleVenta;
use App\Models\MovimientoStock;
use App\Models\Configuracion;
use App\Models\Cupon;
use App\Helpers\PaisHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'vendedor']);

        if ($request->filled('buscar')) {
            $query->where('numero_venta', 'like', "%{$request->buscar}%")
                  ->orWhereHas('cliente', fn($q) =>
                      $q->where('nombre', 'like', "%{$request->buscar}%")
                        ->orWhere('apellido', 'like', "%{$request->buscar}%")
                  );
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }

        $ventas = $query->orderByDesc('fecha_venta')->paginate(15);

        $totalMes = Venta::where('estado', 'completada')
            ->where('fecha_venta', '>=', Carbon::now()->startOfMonth())
            ->sum('total');

        return view('ventas.index', compact('ventas', 'totalMes'));
    }

    public function create()
    {
        $clientes  = Cliente::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'marca'])
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', compact('clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $paisConfig = PaisHelper::configuracionActual();
        $request->validate([
            'cliente_id'          => 'nullable|exists:clientes,id',
            'metodo_pago'         => 'required|in:' . implode(',', $paisConfig['metodos_pago']),
            'productos'           => 'required|array|min:1|max:100',
            'productos.*.id'      => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1|max:1000',
            'productos.*.descuento' => 'nullable|numeric|min:0|max:99999999.99',
            'descuento_general'   => 'nullable|numeric|min:0|max:99999999.99',
            'cupon_codigo'        => 'nullable|string|max:30',
            'notas'               => 'nullable|string|max:2000',
        ]);

        // Obtener tenant_id con fallback
        $tenantId = auth()->user()->tenant_id;
        if (!$tenantId) {
            $tenant = \App\Models\Tenant::first();
            if ($tenant) {
                $tenantId = $tenant->id;
                auth()->user()->update(['tenant_id' => $tenantId]);
            }
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $detalles = [];

            foreach ($request->productos as $item) {
                $producto = Producto::findOrFail($item['id']);

                if ($producto->tenant_id && $producto->tenant_id !== $tenantId) {
                    throw new \Exception('No puedes vender productos de otro tenant.');
                }

                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para: {$producto->nombre}");
                }

                $precioUnitario = $producto->precio_venta;
                $descItem       = isset($item['descuento']) ? (float)$item['descuento'] : 0;
                $subItem        = ($precioUnitario * $item['cantidad']) - $descItem;
                $subtotal      += $subItem;

                $detalles[] = [
                    'producto_id'    => $producto->id,
                    'cantidad'       => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'descuento'      => $descItem,
                    'subtotal'       => $subItem,
                    'imei_vendido'   => $item['imei'] ?? null,
                ];

                // Reducir stock y registrar movimiento
                $stockAnterior = $producto->stock;
                $producto->decrement('stock', $item['cantidad']);

                MovimientoStock::create([
                    'producto_id'    => $producto->id,
                    'tipo'           => 'salida',
                    'motivo'         => 'venta',
                    'cantidad'       => -$item['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $producto->fresh()->stock,
                    'observacion'    => "Venta de {$item['cantidad']} unidad(es)",
                    'user_id'        => Auth::id(),
                    'tenant_id'      => $tenantId,
                ]);
            }

            $descuento = (float)($request->descuento_general ?? 0);
            $cuponAplicado = null;

            // ── Procesar cupón de descuento ──
            if ($request->filled('cupon_codigo')) {
                $codigoCupon = strtoupper(trim($request->cupon_codigo));
                $cupon = Cupon::where('codigo', $codigoCupon)->first();

                if (!$cupon) {
                    throw new \Exception("El cupón '{$codigoCupon}' no existe.");
                }

                if (!$cupon->esValido()) {
                    throw new \Exception("El cupón '{$codigoCupon}' ya fue usado o está expirado.");
                }

                // Aplicar descuento según tipo
                if ($cupon->tipo === 'porcentaje') {
                    $descuentoCupon = round($subtotal * ((float)$cupon->valor / 100), 2);
                } else {
                    // Descuento fijo
                    $descuentoCupon = (float)$cupon->valor;
                }

                $descuento += $descuentoCupon;
                $cuponAplicado = $cupon;
            }

            $base      = $subtotal - $descuento;
            $impuestoPorcentaje = Configuracion::empresa()->igv ?? ($paisConfig['impuesto'] ?? 18);
            $impuesto  = round($base * ($impuestoPorcentaje / 100), 2);
            $total     = $base + $impuesto;

            $venta = Venta::create([
                'numero_venta' => Venta::generarNumero(),
                'cliente_id'   => $request->cliente_id,
                'user_id'      => Auth::id(),
                'fecha_venta'  => now(),
                'subtotal'     => $subtotal,
                'descuento'    => $descuento,
                'impuesto'     => $impuesto,
                'total'        => $total,
                'metodo_pago'  => $request->metodo_pago,
                'estado'       => 'completada',
                'notas'        => $request->notas,
                'tenant_id'    => $tenantId,
            ]);

            foreach ($detalles as $detalle) {
                $detalle['venta_id'] = $venta->id;
                DetalleVenta::create($detalle);
            }

            // Marcar cupón como usado
            if ($cuponAplicado) {
                $cuponAplicado->marcarUsado($venta->id);
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', "Venta {$venta->numero_venta} registrada correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Venta $venta)
    {
        $venta->load(['cliente', 'vendedor', 'detalles.producto.marca']);
        return view('ventas.show', compact('venta'));
    }

    public function printTicket(Venta $venta)
    {
        $venta->load(['cliente', 'vendedor', 'detalles.producto.marca']);

        // Obtener URL de la mini página web
        $tenant = $venta->tenant;
        $urlMiniWeb = $tenant?->slug_publico ? url('/t/' . $tenant->slug_publico) : null;

        return view('ventas.ticket', compact('venta', 'urlMiniWeb'));
    }

    /**
     * Genera el ticket como texto formateado (estilo 80mm) y abre WhatsApp.
     */
    public function enviarWhatsApp(Venta $venta)
    {
        $venta->load(['cliente', 'vendedor', 'detalles.producto.marca']);
        $empresa = Configuracion::empresa();

        // Obtener teléfono del cliente
        $telefono = $venta->cliente?->telefono ?? $venta->cliente?->celular ?? null;
        if (!$telefono) {
            return back()->with('error', 'El cliente no tiene teléfono registrado para enviar por WhatsApp.');
        }

        // Limpiar teléfono: solo dígitos
        $telefono = preg_replace('/\D/', '', $telefono);

        // Si no tiene código de país, agregar el de la configuración
        $pais = $empresa?->pais ?? 'PE';
        $paisConfig = PaisHelper::configuracionPorPais($pais);
        $codigoPais = $paisConfig['codigo_whatsapp'];
        $simbolo    = $empresa?->simbolo_moneda ?? $paisConfig['simbolo_moneda'];
        $nombreImpuesto = $paisConfig['nombre_impuesto'];

        if (strlen($telefono) <= 9) {
            $telefono = $codigoPais . $telefono;
        }

        // Construir el ticket como texto (estilo 80mm)
        $linea = str_repeat('─', 32);
        $lineaDoble = str_repeat('═', 32);

        $texto = $lineaDoble . "\n";
        $texto .= str_pad($empresa?->nombre_tienda ?? 'CRM Celulares', 32, ' ', STR_PAD_BOTH) . "\n";
        if ($empresa?->ruc) $texto .= str_pad('RUC: ' . $empresa->ruc, 32, ' ', STR_PAD_BOTH) . "\n";
        if ($empresa?->direccion) $texto .= str_pad($empresa->direccion, 32, ' ', STR_PAD_BOTH) . "\n";
        if ($empresa?->telefono) $texto .= str_pad('Telf: ' . $empresa->telefono, 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= $lineaDoble . "\n";
        $texto .= str_pad($venta->numero_venta, 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= str_pad($venta->fecha_venta->format('d/m/Y H:i'), 32, ' ', STR_PAD_BOTH) . "\n";
        $texto .= $linea . "\n";

        // Cliente
        $texto .= 'CLIENTE: ' . ($venta->cliente?->nombre_completo ?? 'VENTA GENERAL') . "\n";
        if ($venta->cliente?->telefono) $texto .= 'TEL: ' . $venta->cliente->telefono . "\n";
        $texto .= 'PAGO: ' . ucfirst($venta->metodo_pago) . "\n";
        $texto .= 'VENDEDOR: ' . ($venta->vendedor->name ?? '—') . "\n";
        $texto .= $linea . "\n";

        // Productos
        $texto .= str_pad('PRODUCTO', 20) . str_pad('CANT', 4, ' ', STR_PAD_LEFT) . str_pad('SUBT', 8, ' ', STR_PAD_LEFT) . "\n";
        foreach ($venta->detalles as $det) {
            $nombre = mb_substr($det->producto->nombre ?? '—', 0, 20);
            $texto .= str_pad($nombre, 20) . str_pad($det->cantidad, 4, ' ', STR_PAD_LEFT) . str_pad(number_format($det->subtotal, 2), 8, ' ', STR_PAD_LEFT) . "\n";
            if ($det->imei_vendido) {
                $texto .= str_pad('  IMEI: ' . $det->imei_vendido, 32) . "\n";
            }
        }
        $texto .= $linea . "\n";

        // Totales
        $texto .= str_pad('Subtotal', 24) . str_pad($simbolo . ' ' . number_format($venta->subtotal, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        if ($venta->descuento > 0) {
            $texto .= str_pad('Descuento', 24) . str_pad('-' . $simbolo . ' ' . number_format($venta->descuento, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        }
        $texto .= str_pad($nombreImpuesto . ' (' . ($empresa?->igv ?? 18) . '%)', 24) . str_pad($simbolo . ' ' . number_format($venta->impuesto, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= $lineaDoble . "\n";
        $texto .= str_pad('TOTAL', 24) . str_pad($simbolo . ' ' . number_format($venta->total, 2), 8, ' ', STR_PAD_LEFT) . "\n";
        $texto .= $lineaDoble . "\n";

        // Garantía
        if ($empresa?->terminos_garantia) {
            $texto .= "\nGARANTÍA:\n" . $empresa->terminos_garantia . "\n";
        }

        // Notas
        if ($venta->notas) {
            $texto .= "\nNOTAS: " . $venta->notas . "\n";
        }

        $texto .= "\n" . str_pad('¡Gracias por su preferencia!', 32, ' ', STR_PAD_BOTH) . "\n";

        // URL de la mini página web
        $tenant = $venta->tenant;
        $urlMiniWeb = $tenant?->slug_publico ? url('/t/' . $tenant->slug_publico) : null;
        if ($urlMiniWeb) {
            $texto .= "\n🌐 *Visita nuestra tienda:*\n" . $urlMiniWeb . "\n";
        }

        // URL de WhatsApp
        $url = 'https://wa.me/' . $telefono . '?text=' . urlencode($texto);

        return redirect()->away($url);
    }

    public function cancelar(Venta $venta)
    {
        if ($venta->estado !== 'completada') {
            return back()->with('error', 'Solo se pueden cancelar ventas completadas.');
        }

        DB::transaction(function () use ($venta) {
            foreach ($venta->detalles as $detalle) {
                $producto = $detalle->producto;
                $stockAnterior = $producto->stock;
                $producto->increment('stock', $detalle->cantidad);

                MovimientoStock::create([
                    'producto_id'    => $producto->id,
                    'tipo'           => 'entrada',
                    'motivo'         => 'cancelacion',
                    'cantidad'       => $detalle->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo'    => $stockAnterior + $detalle->cantidad,
                    'observacion'    => "Cancelación de venta {$venta->numero_venta}",
                    'user_id'        => Auth::id(),
                    'tenant_id'      => Auth::user()->tenant_id,
                ]);
            }
            $venta->update(['estado' => 'cancelada']);
        });

        return back()->with('success', 'Venta cancelada y stock restaurado.');
    }
}
