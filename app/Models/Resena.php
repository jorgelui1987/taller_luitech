<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\TenantScope;

class Resena extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'reparacion_id', 'cliente_id', 'calificacion',
        'comentario', 'nombre_publico', 'publicada', 'respondida',
        'respuesta_admin', 'fecha_respuesta',
    ];

    protected $casts = [
        'calificacion' => 'integer',
        'publicada' => 'boolean',
        'respondida' => 'boolean',
        'fecha_respuesta' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($resena) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $resena->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function scopePublicadas($query)
    {
        return $query->where('publicada', true);
    }

    public function scopeConCalificacionMinima($query, int $min = 4)
    {
        return $query->where('calificacion', '>=', $min);
    }
}
