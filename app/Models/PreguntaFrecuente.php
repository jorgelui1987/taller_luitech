<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreguntaFrecuente extends Model
{
    protected $table = 'preguntas_frecuentes';

    protected $fillable = ['pregunta', 'respuesta', 'orden', 'activo'];

    protected $casts = [
        'orden'  => 'integer',
        'activo' => 'boolean',
    ];
}
