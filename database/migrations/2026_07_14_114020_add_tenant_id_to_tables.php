<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tablasConTenant = [
        'clientes',
        'productos',
        'ventas',
        'detalle_ventas',
        'reparaciones',
    ];

    public function up(): void
    {
        foreach ($this->tablasConTenant as $tabla) {
            Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')
                      ->references('id')
                      ->on('tenants')
                      ->onDelete('cascade');
                $table->index('tenant_id');
            });
        }

        // Users también necesita tenant_id
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('cascade');
            $table->index('tenant_id');
        });

        // La tabla configuracion también se relaciona con un tenant
        Schema::table('configuracion', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        $tablas = array_merge($this->tablasConTenant, ['users', 'configuracion']);
        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};