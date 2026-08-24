<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // SQL nativo de PostgreSQL: no requiere doctrine/dbal
            DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN producto_id DROP NOT NULL');
            DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN detalle_venta_id DROP NOT NULL');

            return;
        }

        // SQLite / MySQL / MariaDB: schema builder nativo (Laravel 10+)
        Schema::table('garantia_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('producto_id')->nullable()->change();
            $table->unsignedBigInteger('detalle_venta_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN detalle_venta_id SET NOT NULL');
            DB::statement('ALTER TABLE garantia_detalles ALTER COLUMN producto_id SET NOT NULL');

            return;
        }

        Schema::table('garantia_detalles', function (Blueprint $table) {
            $table->unsignedBigInteger('detalle_venta_id')->nullable(false)->change();
            $table->unsignedBigInteger('producto_id')->nullable(false)->change();
        });
    }
};
