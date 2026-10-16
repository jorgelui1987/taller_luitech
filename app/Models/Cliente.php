<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'apellido', 'email', 'telefono', 'celular', 'dni',
        'rut', 'rut_dv',
        'direccion', 'ciudad', 'fecha_nacimiento', 'tipo', 'empresa',
        'ruc', 'notas', 'activo', 'tenant_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($cliente) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $cliente->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    /**
     * WhatsApp como identificación del cliente.
     * El RUT/DNI es opcional; el teléfono (WhatsApp) es el ID real.
     */
    public function getWhatsappAttribute(): ?string
    {
        return $this->telefono ?: $this->celular;
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $numero = preg_replace('/\D/', '', (string) ($this->telefono ?: $this->celular));
        if ($numero === '') {
            return null;
        }
        // Si es número local chileno (9 dígitos partiendo en 9), anteponer 56
        if (strlen($numero) === 9 && str_starts_with($numero, '9')) {
            $numero = '56' . $numero;
        }
        return 'https://wa.me/' . $numero;
    }

    public function getDocumentoAttribute(): ?string
    {
        if (!empty($this->dni)) {
            return $this->dni . (!empty($this->rut_dv) ? '-' . $this->rut_dv : '');
        }
        if (!empty($this->rut)) {
            return $this->rut . (!empty($this->rut_dv) ? '-' . $this->rut_dv : '');
        }
        return null;
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function reparaciones()
    {
        return $this->hasMany(Reparacion::class);
    }

    public function totalCompras(): float
    {
        return $this->ventas()->where('estado', 'completada')->sum('total');
    }
}
