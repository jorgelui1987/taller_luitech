<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPrecio extends Model
{
    protected $table = 'plan_precios';

    protected $fillable = [
        'plan_key',
        'nombre',
        'precio_mensual',
        'moneda',
        'simbolo',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Obtener todos los planes activos con sus precios.
     * Si la tabla no existe, retorna valores por defecto.
     */
    public static function getPlanesActivos()
    {
        try {
            return static::where('activo', true)->get()->keyBy('plan_key');
        } catch (\Exception $e) {
            // Si la tabla no existe, devolver valores por defecto
            $defaults = [
                'gratis' => (object)[
                    'plan_key' => 'gratis',
                    'nombre' => 'Gratis',
                    'precio_mensual' => 0,
                    'moneda' => 'PEN',
                    'simbolo' => 'S/',
                    'descripcion' => 'Para empezar',
                    'precioFormateado' => function() { return 'S/0'; },
                ],
                'basico' => (object)[
                    'plan_key' => 'basico',
                    'nombre' => 'Básico',
                    'precio_mensual' => 49,
                    'moneda' => 'PEN',
                    'simbolo' => 'S/',
                    'descripcion' => 'Para negocios pequeños',
                    'precioFormateado' => function() { return 'S/49'; },
                ],
                'profesional' => (object)[
                    'plan_key' => 'profesional',
                    'nombre' => 'Profesional',
                    'precio_mensual' => 99,
                    'moneda' => 'PEN',
                    'simbolo' => 'S/',
                    'descripcion' => 'Para negocios en crecimiento',
                    'precioFormateado' => function() { return 'S/99'; },
                ],
                'empresarial' => (object)[
                    'plan_key' => 'empresarial',
                    'nombre' => 'Empresarial',
                    'precio_mensual' => 199,
                    'moneda' => 'PEN',
                    'simbolo' => 'S/',
                    'descripcion' => 'Para grandes tiendas',
                    'precioFormateado' => function() { return 'S/199'; },
                ],
            ];
            return collect($defaults);
        }
    }

    /**
     * Obtener el precio formateado de un plan.
     */
    public function precioFormateado()
    {
        if ($this->precio_mensual == 0) {
            return $this->simbolo . '0';
        }
        return $this->simbolo . number_format($this->precio_mensual, $this->precio_mensual == floor($this->precio_mensual) ? 0 : 2);
    }
}