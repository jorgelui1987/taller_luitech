<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'empresa',
        'subdominio',
        'slug_publico',
        'dominio',
        'email_contacto',
        'telefono_contacto',
        'plan',
        'estado',
        'logo',
        'pais',
        'moneda',
        'simbolo_moneda',
        'impuesto',
        'max_usuarios',
        'max_productos',
        'fecha_expiracion',
        'configuracion_extra',
        'redes_sociales',
        'descripcion_corta',
        'horario_atencion',
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'configuracion_extra' => 'array',
        'redes_sociales' => 'array',
        'max_usuarios' => 'integer',
        'max_productos' => 'integer',
        'impuesto' => 'decimal:2',
    ];

    // ─── Relaciones ────────────────────────────────────────────────
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function configuracion(): HasMany
    {
        return $this->hasMany(Configuracion::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function reparaciones(): HasMany
    {
        return $this->hasMany(Reparacion::class);
    }

    public function cupones(): HasMany
    {
        return $this->hasMany(Cupon::class);
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // ─── Helpers ───────────────────────────────────────────────────
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function estaSuspendido(): bool
    {
        return $this->estado === 'suspendido';
    }

    public function haExpirado(): bool
    {
        return $this->fecha_expiracion && $this->fecha_expiracion->isPast();
    }

    public function puedeAgregarUsuario(): bool
    {
        return $this->usuarios()->count() < $this->max_usuarios;
    }

    public function puedeAgregarProducto(): bool
    {
        return $this->productos()->count() < $this->max_productos;
    }

    public function getUrlPublicaAttribute(): ?string
    {
        if (!$this->slug_publico) {
            return null;
        }
        return url('/t/' . $this->slug_publico);
    }

    /**
     * Obtiene el tenant actual basado en el subdominio/dominio.
     */
    public static function current(): ?self
    {
        $host = request()->getHost();
        $subdominio = explode('.', $host)[0] ?? null;

        if (!$subdominio || $subdominio === 'www' || $subdominio === 'localhost' || $subdominio === '127') {
            // Es el panel principal / superadmin
            return null;
        }

        return static::where('subdominio', $subdominio)
            ->orWhere('dominio', $host)
            ->first();
    }

    /**
     * Obtiene el tenant_id actual (para usar en scopes).
     */
    public static function currentId(): ?int
    {
        $tenant = static::current();
        return $tenant?->id;
    }

    /**
     * Genera un slug público único a partir de un texto base.
     * Si el slug ya existe agrega sufijos: -2, -3, ...
     * Se usa para la URL pública de la Sala de Espera: /pantalla/{slug}.
     */
    public static function generarSlugUnico(?string $base): ?string
    {
        $base = trim((string) $base);
        if ($base === '') {
            return null;
        }

        $slug  = \Illuminate\Support\Str::slug($base) ?: 'tienda';
        $unico = $slug;
        $i     = 2;

        while (static::withoutGlobalScopes()->where('slug_publico', $unico)->exists()) {
            $unico = $slug . '-' . $i++;
        }

        return $unico;
    }

    /**
     * Colores de marca de la empresa (guardados en configuracion_extra).
     * Valores por defecto = tema Luitech.
     */
    public function colores(): array
    {
        $extra = $this->configuracion_extra ?? [];

        $primario   = $extra['color_primario']   ?? '#0891b2';
        $secundario = $extra['color_secundario'] ?? '#3b82f6';

        return [
            'primario'      => $primario,
            'secundario'    => $secundario,
            'primario_rgba' => self::hexARgba($primario, 0.18),
        ];
    }

    /**
     * Oscurece un color hex (para texto/bordes legibles sobre blanco).
     * Factor 0.3 = 30% más oscuro. Formato inválido → color por defecto.
     */
    public static function oscurecerHex(string $hex, float $factor = 0.3): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = '0891b2';
        }

        return sprintf(
            '#%02x%02x%02x',
            (int) max(0, round(hexdec(substr($hex, 0, 2)) * (1 - $factor))),
            (int) max(0, round(hexdec(substr($hex, 2, 2)) * (1 - $factor))),
            (int) max(0, round(hexdec(substr($hex, 4, 2)) * (1 - $factor)))
        );
    }

    /**
     * Convierte #RRGGBB a rgba(r,g,b,alpha).
     * Formato inválido → color por defecto del tema Luitech.
     */
    public static function hexARgba(string $hex, float $alpha = 1.0): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = '0891b2';
        }

        return sprintf(
            'rgba(%d,%d,%d,%.2f)',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
            $alpha
        );
    }
}
