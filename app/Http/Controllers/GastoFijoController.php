<?php

namespace App\Http\Controllers;

use App\Models\GastoFijo;
use Illuminate\Http\Request;

class GastoFijoController extends Controller
{
    public function index(Request $request)
    {
        $query = GastoFijo::orderByDesc('created_at');

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        $gastos = $query->get();
        $totalMensual = GastoFijo::where('activo', true)->sum('monto');
        $categorias = GastoFijo::select('categoria')->distinct()->whereNotNull('categoria')->pluck('categoria');

        return view('gastos.index', compact('gastos', 'totalMensual', 'categorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:150',
            'monto'       => 'required|numeric|min:0',
            'categoria'   => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:500',
            'fecha'       => 'nullable|date',
        ]);

        GastoFijo::create($validated);

        return back()->with('success', 'Gasto fijo registrado correctamente.');
    }

    public function update(Request $request, GastoFijo $gasto)
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:150',
            'monto'       => 'required|numeric|min:0',
            'categoria'   => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:500',
            'fecha'       => 'nullable|date',
        ]);

        $gasto->update($validated);

        return back()->with('success', 'Gasto fijo actualizado correctamente.');
    }

    public function toggle(GastoFijo $gasto)
    {
        $gasto->update(['activo' => !$gasto->activo]);
        $estado = $gasto->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Gasto fijo {$estado} correctamente.");
    }

    public function destroy(GastoFijo $gasto)
    {
        $gasto->delete();
        return back()->with('success', 'Gasto fijo eliminado correctamente.');
    }
}