<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AyudaController extends Controller
{
    /**
     * Muestra la página de ayuda/manual de la aplicación.
     */
    public function index()
    {
        return view('ayuda.index');
    }
}
