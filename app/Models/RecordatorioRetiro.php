<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\TenantScope;

class RecordatorioRetiro extends Model
{
    use HasFactory;

    protected $table = 'recordatorios_retiro';

    protected $fillable = [
        'tenant_id', 'reparacion_id', 'enviado_en', 'tipo', 'telefono',
    ];

    protected $casts = [
        'enviado_en' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($recordatorio) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $recordatorio->tenant_id = auth()->user()->tenant_id;
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
}
