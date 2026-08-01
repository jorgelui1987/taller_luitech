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
        'presupuesto', 'abono', 'costo_final', 'costo_repuesto', 'total',
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
        'costo_repuesto'   => 'decimal:2',
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

    public function calcularTotal(): float
    {
        $precio = ($this->costo_final !== null && $this->costo_final > 0)
            ? (float) $this->costo_final
            : (float) ($this->presupuesto ?? 0);
        $abono = (float) ($this->abono ?? 0);

        return max(0, $precio - $abono);
    }

    /**
     * Base de comisión del técnico = Presupuesto - Costo de Repuesto(s)
     * Ej: Presupuesto 50,000 - Repuesto 10,000 = Base 40,000
     */
    public function baseComision(): float
    {
        $presupuesto   = (float) ($this->presupuesto ?? 0);
        $costoRepuesto = (float) ($this->costo_repuesto ?? 0);

        return max(0, $presupuesto - $costoRepuesto);
    }

    /**
     * Monto de comisión = Base × (% del técnico / 100)
     */
    public function montoComision(): float
    {
        $porcentaje = (float) ($this->comision_porcentaje ?? $this->tecnico?->comision_porcentaje ?? 0);
        if ($porcentaje <= 0) {
            return 0;
        }

        return round($this->baseComision() * ($porcentaje / 100), 2);
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
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $castType = ($driver === 'pgsql' || $driver === 'sqlite') ? 'INTEGER' : 'UNSIGNED';

        // Buscar el número de orden más alto en la base de datos (sin importar tenant_id ni soft deletes)
        $maxNumero = \Illuminate\Support\Facades\DB::table('reparaciones')
            ->whereNotNull('numero_orden')
            ->orderByRaw("CAST(SUBSTRING(numero_orden, 5) AS {$castType}) DESC")
            ->value('numero_orden');

        $numero = 1;
        if ($maxNumero) {
            $numExtraido = (int) preg_replace('/[^0-9]/', '', $maxNumero);
            if ($numExtraido > 0) {
                $numero = $numExtraido + 1;
            }
        }

        // Bucle de seguridad para garantizar unicidad absoluta
        $nuevo = 'RPT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        $contador = 0;
        while (\Illuminate\Support\Facades\DB::table('reparaciones')->where('numero_orden', $nuevo)->exists() && $contador < 1000) {
            $numero++;
            $nuevo = 'RPT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
            $contador++;
        }

        return $nuevo;
    }
}