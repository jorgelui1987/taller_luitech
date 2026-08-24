<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el registro de efectivo recibido y vuelto en ventas.
 *
 * Solo aplica a pagos en EFECTIVO: el vendedor registra con cuánto
 * pagó el cliente y el sistema calcula cuánto devolver.
 * Ambos campos son opcionales: las ventas con tarjeta/Point o las
 * anteriores a esta función quedan con NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('monto_recibido', 12, 2)->nullable()->after('total');
            $table->decimal('vuelto', 12, 2)->nullable()->after('monto_recibido');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['monto_recibido', 'vuelto']);
        });
    }
};