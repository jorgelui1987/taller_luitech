<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar tenant_id a categorias
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->index()->after('id');
        });

        // Agregar tenant_id a marcas
        Schema::table('marcas', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->index()->after('id');
        });

        // Migrar datos existentes: asignar tenant según los productos que las usan
        $this->backfillTenantId('categorias', 'categoria_id');
        $this->backfillTenantId('marcas', 'marca_id');
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('marcas', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }

    /**
     * Asigna tenant_id a las filas existentes basándose en los productos que las usan.
     * Si una fila no tiene productos asociados, se asigna al primer tenant (si existe).
     */
    private function backfillTenantId(string $tabla, string $fkColumn): void
    {
        $items = DB::table($tabla)->whereNull('tenant_id')->get();

        foreach ($items as $item) {
            $tenantId = DB::table('productos')
                ->where($fkColumn, $item->id)
                ->whereNotNull('tenant_id')
                ->value('tenant_id');

            if ($tenantId) {
                DB::table($tabla)->where('id', $item->id)->update(['tenant_id' => $tenantId]);
            }
        }

        // Las que no tienen productos asociados, asignar al primer tenant (si existe)
        $primerTenantId = DB::table('tenants')->value('id');
        if ($primerTenantId) {
            DB::table($tabla)->whereNull('tenant_id')->update(['tenant_id' => $primerTenantId]);
        }
    }
};