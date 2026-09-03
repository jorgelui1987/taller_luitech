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

    /**
     * Estado de la garantía: vigente, vencida o sin garantía.
     */
    public function estadoGarantia(): array
    {
        if (!$this->garantia || $this->dias_garantia <= 0) {
            return ['estado' => 'sin_garantia', 'label' => 'Sin garantía', 'color' => '#9ca3af', 'bg' => '#f3f4f6'];
        }

        $fechaBase = $this->fecha_entrega ?? $this->fecha_recepcion ?? now();
        $fechaVencimiento = $fechaBase->copy()->addDays((int) $this->dias_garantia);

        if ($fechaVencimiento->lt(now())) {
            return [
                'estado' => 'vencida',
                'label' => 'Vencida',
                'color' => '#dc2626',
                'bg' => '#fee2e2',
                'fecha_vencimiento' => $fechaVencimiento,
            ];
        }

        return [
            'estado' => 'vigente',
            'label' => 'Vigente',
            'color' => '#059669',
            'bg' => '#d1fae5',
            'fecha_vencimiento' => $fechaVencimiento,
        ];
    }

    public function getFirmaEntregaUrlAttribute(): ?string
    {
        return $this->firma_entrega ? asset('storage/' . $this->firma_entrega) : null;
    }

    /**
     * Genera un número de orden con sufijo aleatorio anti-adivinanza:
     * RPT-NNNNNN-XXXX (XXXX sin caracteres ambiguos 0/O/1/I/L).
     * La secuencia base sigue siendo global e incremental; las órdenes
     * antiguas (sin sufijo) siguen consultables por su código completo.
     */
    public static function generarNumero(): string
    {
        // Secuencia más alta de la parte base (ignora sufijos aleatorios)
        $maxBase = (int) \Illuminate\Support\Facades\DB::table('reparaciones')
            ->whereNotNull('numero_orden')
            ->pluck('numero_orden')
            ->map(fn ($numero) => (int) (preg_match('/^RPT-(\d{1,6})/', $numero, $m) ? $m[1] : 0))
            ->max();

        $numero = $maxBase + 1;

        // Bucle de seguridad: el sufijo es aleatorio, colisión improbable
        for ($intentos = 0; $intentos < 50; $intentos++) {
            $nuevo = 'RPT-' . str_pad((string) $numero, 6, '0', STR_PAD_LEFT) . '-' . self::sufijoAleatorio();
            if (!\Illuminate\Support\Facades\DB::table('reparaciones')->where('numero_orden', $nuevo)->exists()) {
                return $nuevo;
            }
        }

        // Fallback extremo: avanza la base y reintenta
        return 'RPT-' . str_pad((string) ($numero + 50), 6, '0', STR_PAD_LEFT) . '-' . self::sufijoAleatorio();
    }

    /**
     * Sufijo aleatorio de 4 caracteres sin ambiguos (sin 0, O, 1, I, L)
     * para que el cliente lo lea o copie sin confusiones.
     */
    public static function sufijoAleatorio(): string
    {
        $alfabeto = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $sufijo = '';
        for ($i = 0; $i < 4; $i++) {
            $sufijo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        }

        return $sufijo;
    }
}
