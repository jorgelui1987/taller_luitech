<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'tenant_id', 'numero_factura', 'plan', 'monto', 'moneda',
        'estado', 'fecha_emision', 'fecha_vencimiento', 'fecha_pago',
        'metodo_pago', 'notas',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now());
    }

    public static function generarNumero(): string
    {
        $max = static::max('numero_factura');
        $numero = 1;
        if ($max) {
            $parte = (int) preg_replace('/[^0-9]/', '', $max);
            $numero = $parte + 1;
        }
        return 'FAC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function marcarPagada(?string $metodoPago = null): void
    {
        $this->update([
            'estado' => 'pagada',
            'fecha_pago' => now()->toDateString(),
            'metodo_pago' => $metodoPago,
        ]);
    }
}