<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            if (!Schema::hasColumn('configuracion', 'promo_banner_activo')) {
                $table->boolean('promo_banner_activo')->default(false)->after('referencia_ubicacion');
            }
            if (!Schema::hasColumn('configuracion', 'promo_banner_texto')) {
                $table->string('promo_banner_texto', 255)->nullable()->after('promo_banner_activo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            if (Schema::hasColumn('configuracion', 'promo_banner_activo')) {
                $table->dropColumn('promo_banner_activo');
            }
            if (Schema::hasColumn('configuracion', 'promo_banner_texto')) {
                $table->dropColumn('promo_banner_texto');
            }
        });
    }
};