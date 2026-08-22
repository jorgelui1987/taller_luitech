<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Reparacion extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $table = 'reparaciones';

    protected $fillable = [
        'numero_orden', 'cliente_id', 'tecnico_id',
        'tipo_dispositivo', 'dispositivo', 'codigo_equipo', 'tipo_codigo', 'patron_secuencia',
        'marca', 'modelo', 'imei', 'color',
        'falla_reportada', 'diagnostico', 'solucion',
        'presupuesto', 'abono', 'costo_final', 'costo_repuesto', 'impuesto', 'total',
        'cupon_codigo',
        'estado', 'prioridad', 'metodo_pago',
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
        'impuesto'         => 'decimal:2',
        'total'            => 'decimal:2',
        'garantia'         => 'boolean',
        'comision_porcentaje' => 'decimal:2',
        'comision_monto'   => 'decimal:2',
        'comision_pagada'  => 'boolean',
    ];

    protected $dates = ['deleted_at'];


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

    public function cupones()
    {
        return $this->hasMany(Cupon::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function recordatoriosRetiro()
    {
        return $this->hasMany(RecordatorioRetiro::class);
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
     * Base de comision del tecnico = Monto cobrado - Costo de Repuesto(s)
     * Usa costo_final si existe, de lo contrario presupuesto.
     */
    public function baseComision(): float
    {
        $cobrado = ($this->costo_final !== null && $this->costo_final > 0)
            ? (float) $this->costo_final
            : (float) ($this->presupuesto ?? 0);
        $costoRepuesto = (float) ($this->costo_repuesto ?? 0);

        return max(0, $cobrado - $costoRepuesto);
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
            $numExtraido = (int) preg_replace('/\D/', '', $maxNumero);
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
