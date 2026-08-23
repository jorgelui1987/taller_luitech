<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQL nativo de PostgreSQL: no requiere doctrine/dbal
        DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN producto_id DROP NOT NULL');
        DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN detalle_venta_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN detalle_venta_id SET NOT NULL');
        DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN producto_id SET NOT NULL');
    }
};