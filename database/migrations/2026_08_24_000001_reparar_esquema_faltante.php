<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Migración de REPARACIÓN idempotente del esquema.
 *
 * Contexto: si la base de datos fue restaurada desde un backup antiguo
 * (generado antes de las migraciones recientes), las tablas quedan sin las
 * columnas/tablas nuevas. Como los backups NO incluyen la tabla `migrations`,
 * Laravel cree que todo está migrado y las migraciones originales nunca se
 * vuelven a ejecutar → errores 500 al acceder a columnas inexistentes.
 *
 * Esta migración verifica cada columna/tabla y SOLO agrega lo que falte,
 * reparando el esquema sin tocar los datos existentes. Es segura ejecutarla
 * cualquier cantidad de veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agregarColumnas = function (string $tabla, array $columnas): void {
            if (!Schema::hasTable($tabla)) {
                return;
            }

            $faltantes = array_filter(
                $columnas,
                fn ($def, $col) => !Schema::hasColumn($tabla, $col),
                ARRAY_FILTER_USE_BOTH
            );

            if (empty($faltantes)) {
                return;
            }

            Schema::table($tabla, function (Blueprint $table) use ($faltantes) {
                foreach ($faltantes as $definir) {
                    $definir($table);
                }
            });

            Log::info("Migración de reparación: columnas agregadas a {$tabla}", [
                'columnas' => array_keys($faltantes),
            ]);
        };

        // ── configuracion: facturación electrónica + Mercado Pago ──
        $agregarColumnas('configuracion', [
            'facturacion_electronica_activa' => fn (Blueprint $t) => $t->boolean('facturacion_electronica_activa')->default(false),
            'certificado_password'           => fn (Blueprint $t) => $t->string('certificado_password')->nullable(),
            'mercadopago_activo'             => fn (Blueprint $t) => $t->boolean('mercadopago_activo')->default(false),
            'mercadopago_public_key'         => fn (Blueprint $t) => $t->string('mercadopago_public_key')->nullable(),
            'mercadopago_access_token'       => fn (Blueprint $t) => $t->string('mercadopago_access_token')->nullable(),
            'mercadopago_device_id'          => fn (Blueprint $t) => $t->string('mercadopago_device_id')->nullable(),
            'mercadopago_webhook_secret'     => fn (Blueprint $t) => $t->string('mercadopago_webhook_secret')->nullable(),
        ]);

        // ── ventas ──
        $agregarColumnas('ventas', [
            'estado_pago'        => fn (Blueprint $t) => $t->string('estado_pago')->default('pendiente'),
            'motivo_cancelacion' => fn (Blueprint $t) => $t->string('motivo_cancelacion')->nullable(),
        ]);

        // ── reparaciones ──
        $agregarColumnas('reparaciones', [
            'metodo_pago' => fn (Blueprint $t) => $t->string('metodo_pago', 30)->nullable(),
            'impuesto'    => fn (Blueprint $t) => $t->decimal('impuesto', 10, 2)->default(0),
        ]);

        // ── productos ──
        $agregarColumnas('productos', [
            'stock_daniado' => fn (Blueprint $t) => $t->integer('stock_daniado')->default(0),
        ]);

        // ── Tablas que podrían no existir si el backup era muy antiguo ──

        if (!Schema::hasTable('cierres_caja')) {
            Schema::create('cierres_caja', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->decimal('monto_inicial', 10, 2)->default(0);
                $table->timestamp('fecha_apertura')->nullable();
                $table->timestamp('fecha_cierre')->nullable();
                $table->decimal('ventas_efectivo', 10, 2)->default(0);
                $table->decimal('ventas_tarjeta', 10, 2)->default(0);
                $table->decimal('ventas_transferencia', 10, 2)->default(0);
                $table->decimal('ventas_otros', 10, 2)->default(0);
                $table->decimal('total_ingresos', 10, 2)->default(0);
                $table->decimal('total_egresos', 10, 2)->default(0);
                $table->decimal('total_esperado', 10, 2)->default(0);
                $table->decimal('total_contado', 10, 2)->nullable();
                $table->decimal('diferencia', 10, 2)->default(0);
                $table->string('estado', 20)->default('abierta');
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'estado']);
                $table->index('user_id');
                $table->index('fecha_apertura');
            });
        }

        if (!Schema::hasTable('garantias')
            && Schema::hasTable('ventas')
            && Schema::hasTable('clientes')
            && Schema::hasTable('users')
            && Schema::hasTable('tenants')) {
            Schema::create('garantias', function (Blueprint $table) {
                $table->id();
                $table->string('numero_garantia')->unique();
                $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
                $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('fecha_garantia');
                $table->string('motivo')->default('garantia');
                $table->enum('estado', ['completada', 'anulada'])->default('completada');
                $table->text('observacion')->nullable();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->timestamps();
                $table->index(['venta_id', 'estado']);
                $table->index('tenant_id');
            });
        }

        if (!Schema::hasTable('garantia_detalles')
            && Schema::hasTable('garantias')
            && Schema::hasTable('productos')
            && Schema::hasTable('detalle_ventas')) {
            Schema::create('garantia_detalles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('garantia_id')->constrained('garantias')->cascadeOnDelete();
                $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
                $table->foreignId('detalle_venta_id')->constrained('detalle_ventas')->cascadeOnDelete();
                $table->integer('cantidad')->default(1);
                $table->string('condicion')->default('nuevo');
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->timestamps();
                $table->index(['garantia_id', 'producto_id']);
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        // No se revierte: esta migración solo repara el esquema.
        // Eliminar columnas podría destruir datos reales.
    }
};