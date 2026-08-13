<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Marca extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'logo', 'activo', 'tenant_id'];

    protected $casts = ['activo' => 'boolean'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
