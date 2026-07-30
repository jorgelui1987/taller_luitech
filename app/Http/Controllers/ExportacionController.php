<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportacionController extends Controller
{
    public function productosExcel(Request $request)
    {
        $query = Producto::with(['categoria', 'marca'])->where('activo', true);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('marca_id')) {
            $query->where('marca_id', $request->marca_id);
        }

        if ($request->filled('condicion')) {
            $query->where('condicion', $request->condicion);
        }

        if ($request->filled('stock_bajo') && $request->stock_bajo) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        $productos = $query->orderBy('nombre')->get();

        // Generar CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="inventario-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($productos) {
            $output = fopen('php://output', 'w');

            // BOM para Excel (UTF-8)
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Cabeceras
            fputcsv($output, [
                'Código SKU',
                'Código Barras',
                'Nombre',
                'Categoría',
                'Marca',
                'Proveedor',
                'Modelo',
                'Color',
                'Almacenamiento',
                'RAM',
                'Condición',
                'Garantía (días)',
                'Precio Compra',
                'Precio Venta',
                'Stock Actual',
                'Stock Mínimo',
                'Estado Stock',
                'Valor en Stock (Venta)',
                'Margen (%)',
                'Ganancia Unitaria',
                'IMEI',
                'Descripción',
            ]);

            foreach ($productos as $p) {
                $estadoStock = $p->stock <= 0 ? 'Sin stock' : ($p->tieneStockBajo() ? 'Stock bajo' : 'OK');

                fputcsv($output, [
                    $p->codigo,
                    $p->codigo_barras ?? '',
                    $p->nombre,
                    $p->categoria->nombre ?? '',
                    $p->marca->nombre ?? '',
                    $p->proveedor->nombre ?? '',
                    $p->modelo ?? '',
                    $p->color ?? '',
                    $p->almacenamiento ?? '',
                    $p->ram ?? '',
                    ucfirst($p->condicion),
                    $p->garantia_dias ?? '0',
                    number_format($p->precio_compra, 2, '.', ''),
                    number_format($p->precio_venta, 2, '.', ''),
                    $p->stock,
                    $p->stock_minimo,
                    $estadoStock,
                    number_format($p->stock * $p->precio_venta, 2, '.', ''),
                    number_format($p->margen, 1, '.', ''),
                    number_format($p->precio_venta - $p->precio_compra, 2, '.', ''),
                    $p->imei ?? '',
                    $p->descripcion ?? '',
                ]);
            }

            fclose($output);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function productosPDF(Request $request)
    {
        $productos = Producto::with(['categoria', 'marca'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $html = '<html><head><meta charset="utf-8"><style>
            body { font-family: Arial, sans-serif; font-size: 11px; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #a855f7; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
            td { padding: 4px 8px; border-bottom: 1px solid #ddd; }
            h1 { font-size: 18px; color: #1e1b4b; margin-bottom: 5px; }
            .subtitle { color: #666; font-size: 12px; margin-bottom: 15px; }
            .sin-stock { color: #dc2626; font-weight: bold; }
            .stock-bajo { color: #d97706; font-weight: bold; }
            .ok { color: #059669; }
            .total { font-weight: bold; margin-top: 10px; font-size: 13px; }
        </style></head><body>';
        $html .= '<h1>Reporte de Inventario</h1>';
        $html .= '<div class="subtitle">Generado: ' . date('d/m/Y H:i') . ' | Total productos: ' . $productos->count() . '</div>';
        $html .= '<table>';
        $html .= '<tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Marca</th><th>Stock</th><th>P. Compra</th><th>P. Venta</th><th>Estado</th></tr>';

        $valorTotal = 0;
        foreach ($productos as $p) {
            $estado = $p->stock <= 0 ? '<span class="sin-stock">Sin stock</span>'
                    : ($p->tieneStockBajo() ? '<span class="stock-bajo">Stock bajo (' . $p->stock . ')</span>'
                    : '<span class="ok">OK (' . $p->stock . ')</span>');
            $valorTotal += $p->stock * $p->precio_venta;

            $html .= '<tr>';
            $html .= '<td>' . e($p->codigo) . '</td>';
            $html .= '<td>' . e($p->nombre) . '</td>';
            $html .= '<td>' . e($p->categoria->nombre ?? '') . '</td>';
            $html .= '<td>' . e($p->marca->nombre ?? '') . '</td>';
            $html .= '<td>' . $p->stock . '</td>';
            $html .= '<td>S/ ' . number_format($p->precio_compra, 2) . '</td>';
            $html .= '<td>S/ ' . number_format($p->precio_venta, 2) . '</td>';
            $html .= '<td>' . $estado . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '<div class="total">Valor total del inventario (a precio venta): S/ ' . number_format($valorTotal, 2) . '</div>';
        $html .= '</body></html>';

        // Forzar descarga como HTML (se puede abrir con Excel o navegador para imprimir)
        return Response::make($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="inventario-' . date('Y-m-d') . '.html"',
        ]);
    }

    public function productosPlantillaImportacion()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="plantilla-importacion-productos.csv"',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, [
                'codigo',
                'nombre',
                'categoria_id',
                'marca_id',
                'modelo',
                'color',
                'almacenamiento',
                'ram',
                'condicion',
                'precio_compra',
                'precio_venta',
                'stock',
                'stock_minimo',
                'imei',
                'descripcion',
            ]);

            // Fila de ejemplo
            fputcsv($output, [
                'SAM-A54-128',
                'Samsung Galaxy A54 128GB',
                '1',
                '1',
                'A54',
                'Negro',
                '128GB',
                '6GB',
                'nuevo',
                '850.00',
                '1200.00',
                '10',
                '3',
                '',
                'Smartphone Samsung Galaxy A54 5G 128GB',
            ]);

            fclose($output);
        };

        return Response::stream($callback, 200, $headers);
    }
}