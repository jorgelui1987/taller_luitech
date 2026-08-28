<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotizadorPrecio extends Model
{
    protected $table = 'cotizador_precios';

    protected $fillable = [
        'servicio', 'servicio_label', 'dispositivo', 'dispositivo_label',
        'precio_min', 'precio_max', 'orden', 'activo',
    ];

    protected $casts = [
        'precio_min' => 'integer',
        'precio_max' => 'integer',
        'orden'      => 'integer',
        'activo'     => 'boolean',
    ];
}
