<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    /**
     * Mostrar el registro de auditoría.
     */
    public function index(Request $request)
    {
        $query = Auditoria::with('usuario')
            ->when($request->filled('accion'), fn($q) => $q->where('accion', 'like', "%{$request->accion}%"))
            ->when($request->filled('usuario'), fn($q) => $q->whereHas('usuario', fn($u) => $u->where('name', 'like', "%{$request->usuario}%")))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('created_at', '>=', $request->desde))
            ->when($request->filled('hasta'), fn($q) => $q->whereDate('created_at', '<=', $request->hasta))
            ->orderBy('created_at', 'desc');

        $auditorias = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => Auditoria::count(),
            'hoy'   => Auditoria::whereDate('created_at', today())->count(),
            'acciones' => Auditoria::select('accion')->distinct()->count(),
        ];

        return view('auditoria.index', compact('auditorias', 'stats'));
    }
}
