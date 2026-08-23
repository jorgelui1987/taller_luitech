<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Garantia extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'garantias';

    protected $fillable = [
        'numero_garantia', 'venta_id', 'cliente_id', 'user_id',
        'fecha_garantia', 'motivo', 'estado', 'observacion', 'tenant_id',
    ];

    protected $casts = [
        'fecha_garantia' => 'datetime',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(GarantiaDetalle::class);
    }

    public static function generarNumero(): string
    {
        $maxNumero = \Illuminate\Support\Facades\DB::table('garantias')
            ->where('numero_garantia', 'like', 'GAR-%')
            ->max('numero_garantia');

        $numero = 1;
        if ($maxNumero) {
            $parte = substr($maxNumero, 4);
            if (is_numeric($parte)) {
                $numero = (int)$parte + 1;
            }
        }

        $nuevo = 'GAR-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        $contador = 0;
        while (\Illuminate\Support\Facades\DB::table('garantias')
            ->where('numero_garantia', $nuevo)->exists() && $contador < 1000) {
            $numero++;
            $nuevo = 'GAR-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
            $contador++;
        }

        return $nuevo;
    }
}