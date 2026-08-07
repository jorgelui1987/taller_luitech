<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReparacionFoto extends Model
{
    protected $table = 'reparacion_fotos';

    protected $fillable = [
        'reparacion_id',
        'ruta',
        'tipo',
    ];

    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->ruta);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->url;
    }
}