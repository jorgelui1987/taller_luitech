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
            $resultado = $this->procesarArchivo($handle, $headers);

            fclose($handle);

            if (empty($resultado['errores']) && $resultado['importados'] === 0) {
                DB::rollBack();
                return back()->with('error', 'No se pudo importar ningún producto.');
            }

            DB::commit();

            $mensaje = "{$resultado['importados']} productos importados/actualizados correctamente.";
            if (!empty($resultado['errores'])) {
                $mensaje .= ' Advertencias: ' . implode('; ', array_slice($resultado['errores'], 0, 5));
            }

            return redirect()->route('productos.index')->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    private function procesarArchivo($handle, array $headers): array
    {
        $importados = 0;
        $errores = [];
        $linea = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $linea++;
            $data = $this->mapearFila($headers, $row);

            // Validar datos mínimos
            if (empty($data['codigo']) || empty($data['nombre'])) {
                $errores[] = "Línea {$linea}: falta código o nombre";
                continue;
            }

            $productoData = $this->construirProductoData($data);

            $existente = Producto::where('codigo', $data['codigo'])->first();

            if ($existente) {
                $this->actualizarProducto($existente, $productoData);
            } else {
                $this->crearProducto($productoData);
            }

            $importados++;
        }

        return ['importados' => $importados, 'errores' => $errores];
    }

    private function mapearFila(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            if (isset($row[$index])) {
                $data[$header] = trim($row[$index]);
            }
        }
        return $data;
    }

    private function construirProductoData(array $data): array
    {
        return [
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
    }

    private function actualizarProducto(Producto $existente, array $productoData): void
    {
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
    }

    private function crearProducto(array $productoData): void
    {
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
    }
}
