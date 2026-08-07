<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $fillable = [
        'tenant_id', 'user_id', 'accion', 'entidad', 'entidad_id',
        'detalle', 'ip', 'user_agent',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
