<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportacionController extends Controller
{
    public function importarForm()
    {
        return view('productos.importar');
    }

    public function importarStore(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $archivo = $request->file('archivo');
        $path = $archivo->getRealPath();

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'No se pudo leer el archivo.');
        }

        // Detectar y saltar BOM
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }

        // Leer cabeceras
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'El archivo CSV no tiene cabeceras válidas.');
        }

        // Limpiar cabeceras (quitar BOM si quedó)
        $headers = array_map(function ($h) {
            return trim(strtolower(str_replace([' ', '-'], '_', $h)));
        }, $headers);

        // Mapeo de columnas esperadas
        $columnas = [
            'codigo', 'nombre', 'categoria_id', 'marca_id', 'modelo', 'color',
            'almacenamiento', 'ram', 'condicion', 'precio_compra', 'precio_venta',
            'stock', 'stock_minimo', 'imei', 'descripcion',
        ];

        // Verificar que al menos tenga las columnas obligatorias
        $required = ['codigo', 'nombre', 'precio_compra', 'precio_venta'];
        $hasRequired = true;
        foreach ($required as $col) {
            if (!in_array($col, $headers)) {
                $hasRequired = false;
                break;
            }
        }

        if (!$hasRequired) {
            fclose($handle);
            return back()->with('error', 'El CSV debe contener al menos: codigo, nombre, precio_compra, precio_venta. Columnas encontradas: ' . implode(', ', $headers));
        }

        DB::beginTransaction();
        try {
            $importados = 0;
            $errores = [];
            $linea = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $linea++;
                $data = [];

                foreach ($headers as $index => $header) {
                    if (isset($row[$index])) {
                        $data[$header] = trim($row[$index]);
                    }
                }

                // Validar datos mínimos
                if (empty($data['codigo']) || empty($data['nombre'])) {
                    $errores[] = "Línea {$linea}: falta código o nombre";
                    continue;
                }

                // Verificar si ya existe el producto por código
                $existente = Producto::where('codigo', $data['codigo'])->first();

                $productoData = [
                    'codigo'        => $data['codigo'],
                    'nombre'        => $data['nombre'],
                    'descripcion'   => $data['descripcion'] ?? null,
                    'categoria_id'  => $data['categoria_id'] ?? 1,
                    'marca_id'      => $data['marca_id'] ?? 1,
                    'modelo'        => $data['modelo'] ?? null,
                    'color'         => $data['color'] ?? null,
                    'almacenamiento' => $data['almacenamiento'] ?? null,
                    'ram'           => $data['ram'] ?? null,
                    'precio_compra' => (float)($data['precio_compra'] ?? 0),
                    'precio_venta'  => (float)($data['precio_venta'] ?? 0),
                    'stock'         => (int)($data['stock'] ?? 0),
                    'stock_minimo'  => (int)($data['stock_minimo'] ?? 5),
                    'imei'          => $data['imei'] ?? null,
                    'condicion'     => in_array($data['condicion'] ?? '', ['nuevo', 'reacondicionado', 'usado']) ? $data['condicion'] : 'nuevo',
                    'activo'        => true,
                ];

                if ($existente) {
                    // Actualizar producto existente
                    $stockAnterior = $existente->stock;
                    $existente->update($productoData);

                    // Registrar movimiento si cambió el stock
                    if ($existente->stock != $stockAnterior) {
                        MovimientoStock::create([
                            'producto_id'    => $existente->id,
                            'tipo'           => 'ajuste',
                            'motivo'         => 'ajuste',
                            'cantidad'       => $existente->stock - $stockAnterior,
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo'    => $existente->stock,
                            'observacion'    => 'Actualizado por importación CSV',
                            'user_id'        => auth()->id(),
                        ]);
                    }

                    $importados++;
                } else {
                    // Crear nuevo producto
                    $producto = Producto::create($productoData);

                    // Registrar movimiento de entrada inicial
                    if ($productoData['stock'] > 0) {
                        MovimientoStock::create([
                            'producto_id'    => $producto->id,
                            'tipo'           => 'entrada',
                            'motivo'         => 'compra',
                            'cantidad'       => $productoData['stock'],
                            'stock_anterior' => 0,
                            'stock_nuevo'    => $productoData['stock'],
                            'observacion'    => 'Importado por CSV',
                            'user_id'        => auth()->id(),
                        ]);
                    }

                    $importados++;
                }
            }

            fclose($handle);

            if (count($errores) > 0 && $importados === 0) {
                DB::rollBack();
                return back()->with('error', 'No se pudo importar ningún producto. Errores: ' . implode(' | ', array_slice($errores, 0, 5)));
            }

            DB::commit();

            $mensaje = "{$importados} productos importados/actualizados correctamente.";
            if (count($errores) > 0) {
                $mensaje .= ' Advertencias: ' . implode('; ', array_slice($errores, 0, 5));
            }

            return redirect()->route('productos.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }
}