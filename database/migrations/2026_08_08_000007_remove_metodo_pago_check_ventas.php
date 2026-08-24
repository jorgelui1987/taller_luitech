<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // La constraint fue creada con SQL nativo de PostgreSQL;
        // en otros motores (SQLite/MySQL) no existe y se omite.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Eliminar la constraint que impide guardar 'mercadopago' como método de pago
        DB::statement('ALTER TABLE ventas DROP CONSTRAINT IF EXISTS ventas_metodo_pago_check');
    }

    public function down(): void
    {
        // No se puede restaurar fácilmente la constraint original
    }
};