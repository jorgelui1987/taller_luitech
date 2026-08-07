<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Slug público para la tienda (Página Pública)
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('slug_publico')->nullable()->unique()->after('subdominio');
            $table->string('redes_sociales')->nullable()->after('configuracion_extra');
            $table->text('descripcion_corta')->nullable()->after('redes_sociales');
            $table->string('horario_atencion')->nullable()->after('descripcion_corta');
        });

        // Tabla de Cupones de Descuento
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('reparacion_id')->nullable()->constrained('reparaciones')->nullOnDelete();
            $table->string('codigo', 30)->unique();
            $table->string('tipo', 20)->default('porcentaje');
            $table->decimal('valor', 10, 2)->default(10);
            $table->string('descripcion')->nullable();
            $table->dateTime('fecha_expiracion')->nullable();
            $table->dateTime('fecha_uso')->nullable();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->string('estado', 20)->default('activo');
            $table->boolean('compartible')->default(true);
            $table->timestamps();
            $table->index(['tenant_id', 'estado']);
            $table->index(['codigo']);
        });

        // Tabla de Reseñas / Testimonios
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('reparacion_id')->nullable()->constrained('reparaciones')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->tinyInteger('calificacion')->default(5);
            $table->text('comentario')->nullable();
            $table->string('nombre_publico')->nullable();
            $table->boolean('publicada')->default(true);
            $table->boolean('respondida')->default(false);
            $table->text('respuesta_admin')->nullable();
            $table->dateTime('fecha_respuesta')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'publicada']);
        });

        // Campos en configuracion para la tienda pública
        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('terminos_garantia');
            $table->string('facebook')->nullable()->after('instagram');
            $table->string('tiktok')->nullable()->after('facebook');
            $table->string('horario_atencion')->nullable()->after('tiktok');
            $table->text('descripcion_corta')->nullable()->after('horario_atencion');
            $table->boolean('pagina_publica_activa')->default(true)->after('descripcion_corta');
            $table->boolean('cupon_automatico_al_entregar')->default(true)->after('pagina_publica_activa');
            $table->decimal('cupon_descuento_porcentaje', 5, 2)->default(10.00)->after('cupon_automatico_al_entregar');
            $table->integer('cupon_dias_validez')->default(30)->after('cupon_descuento_porcentaje');
        });

        // Registro de recordatorios de retiro enviados
        Schema::create('recordatorios_retiro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('reparacion_id')->constrained('reparaciones')->cascadeOnDelete();
            $table->dateTime('enviado_en');
            $table->string('tipo', 20)->default('recordatorio');
            $table->string('telefono')->nullable();
            $table->timestamps();
            $table->unique(['reparacion_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_retiro');

        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'instagram', 'facebook', 'tiktok', 'horario_atencion',
                'descripcion_corta', 'pagina_publica_activa',
                'cupon_automatico_al_entregar', 'cupon_descuento_porcentaje',
                'cupon_dias_validez',
            ]);
        });

        Schema::dropIfExists('resenas');
        Schema::dropIfExists('cupones');

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['slug_publico', 'redes_sociales', 'descripcion_corta', 'horario_atencion']);
        });
    }
};
