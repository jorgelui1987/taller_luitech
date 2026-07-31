<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;

class Reparacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reparaciones';

    protected $fillable = [
        'numero_orden', 'cliente_id', 'tecnico_id',
        'tipo_dispositivo', 'dispositivo', 'codigo_equipo', 'tipo_codigo', 'patron_secuencia',
        'marca', 'modelo', 'imei', 'color',
        'falla_reportada', 'diagnostico', 'solucion',
        'presupuesto', 'abono', 'costo_final', 'total',
        'estado', 'prioridad',
        'fecha_recepcion', 'fecha_estimada', 'fecha_entrega',
        'garantia', 'dias_garantia', 'notas',
        'firma_recepcion', 'firma_entrega',
        'comision_porcentaje', 'comision_monto', 'comision_pagada', 'comision_fecha_pago',
        'tenant_id',
    ];

    protected $casts = [
        'fecha_recepcion'  => 'datetime',
        'fecha_estimada'   => 'datetime',
        'fecha_entrega'    => 'datetime',
        'presupuesto'      => 'decimal:2',
        'abono'            => 'decimal:2',
        'costo_final'      => 'decimal:2',
        'total'            => 'decimal:2',
        'garantia'         => 'boolean',
        'comision_porcentaje' => 'decimal:2',
        'comision_monto'   => 'decimal:2',
        'comision_pagada'  => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($reparacion) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $reparacion->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function fotos()
    {
        return $this->hasMany(ReparacionFoto::class);
    }

    public function getFirmaRecepcionUrlAttribute(): ?string
    {
        return $this->firma_recepcion ? asset('storage/' . $this->firma_recepcion) : null;
    }

    public function getFirmaEntregaUrlAttribute(): ?string
    {
        return $this->firma_entrega ? asset('storage/' . $this->firma_entrega) : null;
    }

    public static function generarNumero(): string
    {
        $ultimo = self::orderByRaw('CAST(SUBSTRING(numero_orden, 5) AS UNSIGNED) DESC')->lockForUpdate()->first();
        $numero = $ultimo ? (int) substr($ultimo->numero_orden, 4) + 1 : 1;
        return 'RPT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}