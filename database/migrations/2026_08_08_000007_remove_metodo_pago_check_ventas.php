<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar la constraint que impide guardar 'mercadopago' como método de pago
        DB::statement('ALTER TABLE ventas DROP CONSTRAINT IF EXISTS ventas_metodo_pago_check');
    }

    public function down(): void
    {
        // No se puede restaurar fácilmente la constraint original
    }
};