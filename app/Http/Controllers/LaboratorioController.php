<?php

namespace App\Http\Controllers;

use App\Models\CotizadorPrecio;
use App\Models\PreguntaFrecuente;
use Illuminate\Http\Request;

class LaboratorioController extends Controller
{
    public function index()
    {
        return view('laboratorio.index', [
            'precios' => CotizadorPrecio::orderBy('orden')->orderBy('id')->get(),
            'faqs'    => PreguntaFrecuente::orderBy('orden')->orderBy('id')->get(),
        ]);
    }

    public function guardarPrecios(Request $request)
    {
        $datos = $request->input('precios', []);

        foreach ($datos as $id => $fila) {
            $precio = CotizadorPrecio::find($id);
            if (!$precio) {
                continue;
            }
            $min = max(0, (int) ($fila['precio_min'] ?? $precio->precio_min));
            $max = max($min, (int) ($fila['precio_max'] ?? $precio->precio_max));
            $precio->update([
                'servicio_label'    => trim($fila['servicio_label'] ?? $precio->servicio_label),
                'dispositivo_label' => trim($fila['dispositivo_label'] ?? $precio->dispositivo_label),
                'precio_min'        => $min,
                'precio_max'        => $max,
                'activo'            => !empty($fila['activo']),
            ]);
        }

        return back()->with('success', 'Precios del cotizador actualizados. La portada se actualiza al instante.');
    }

    public function agregarPrecio(Request $request)
    {
        $request->validate([
            'servicio_label' => 'required|string|max:100',
            'servicio'       => 'required|string|max:50|regex:/^[a-z0-9_]+$/',
            'dispositivo'    => 'required|string|max:50',
            'precio_min'     => 'required|integer|min:0',
            'precio_max'     => 'required|integer|min:0|gte:precio_min',
        ]);

        $dispositivos = [
            'celular'  => 'Celular / Smartphone',
            'tablet'   => 'Tablet',
            'notebook' => 'Notebook',
            'pc'       => 'PC de Escritorio',
            'consola'  => 'Consola',
        ];

        CotizadorPrecio::create([
            'servicio'          => $request->servicio,
            'servicio_label'    => trim($request->servicio_label),
            'dispositivo'       => $request->dispositivo,
            'dispositivo_label' => $dispositivos[$request->dispositivo] ?? ucfirst($request->dispositivo),
            'precio_min'        => (int) $request->precio_min,
            'precio_max'        => (int) $request->precio_max,
            'orden'             => (int) CotizadorPrecio::max('orden') + 1,
            'activo'            => true,
        ]);

        return back()->with('success', 'Precio agregado al cotizador.');
    }

    public function eliminarPrecio(CotizadorPrecio $precio)
    {
        $precio->delete();

        return back()->with('success', 'Precio eliminado.');
    }

    public function guardarFaq(Request $request)
    {
        $datos = $request->input('faqs', []);

        foreach ($datos as $id => $fila) {
            $faq = PreguntaFrecuente::find($id);
            if (!$faq) {
                continue;
            }
            $faq->update([
                'pregunta' => trim($fila['pregunta'] ?? $faq->pregunta),
                'respuesta' => trim($fila['respuesta'] ?? $faq->respuesta),
                'activo'    => !empty($fila['activo']),
            ]);
        }

        return back()->with('success', 'Preguntas frecuentes actualizadas. La portada se actualiza al instante.');
    }

    public function agregarFaq(Request $request)
    {
        $request->validate([
            'pregunta'  => 'required|string|max:200',
            'respuesta' => 'required|string|max:1500',
        ]);

        PreguntaFrecuente::create([
            'pregunta'  => trim($request->pregunta),
            'respuesta' => trim($request->respuesta),
            'orden'     => (int) PreguntaFrecuente::max('orden') + 1,
            'activo'    => true,
        ]);

        return back()->with('success', 'Pregunta frecuente agregada.');
    }

    public function eliminarFaq(PreguntaFrecuente $faq)
    {
        $faq->delete();

        return back()->with('success', 'Pregunta eliminada.');
    }
}
