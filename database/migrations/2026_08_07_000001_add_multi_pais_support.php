<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Configuración: agregar país y campos de facturación electrónica ──
        Schema::table('configuracion', function (Blueprint $table) {
            $table->string('pais')->default('PE')->after('simbolo_moneda');
            $table->string('rut_emisor')->nullable()->after('pais');
            $table->string('razon_social')->nullable()->after('rut_emisor');
            $table->string('giro')->nullable()->after('razon_social');
            $table->string('comuna_ciudad')->nullable()->after('giro');
            $table->string('proveedor_dte')->nullable()->after('comuna_ciudad'); // acepta, fove, tundra
            $table->string('dte_certificado')->nullable()->after('proveedor_dte');
        });

        // ── Clientes: agregar RUT (Chile) y mantener DNI/RUC (Perú) ──
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('rut')->nullable()->after('dni');
            $table->string('rut_dv')->nullable()->after('rut'); // dígito verificador
        });

        // ── Proveedores: agregar RUT ──
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('rut')->nullable()->after('ruc');
        });

        // ── Ventas: agregar campos DTE (facturación electrónica Chile) ──
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('dte_tipo')->nullable()->after('notas'); // 33=Factura, 39=Boleta
            $table->string('dte_folio')->nullable()->after('dte_tipo');
            $table->string('dte_token')->nullable()->after('dte_folio');
            $table->string('dte_pdf_url')->nullable()->after('dte_token');
            $table->string('dte_estado')->nullable()->after('dte_pdf_url'); // pendiente, aceptado, rechazado
        });
    }

    public function down(): void
    {
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn(['pais', 'rut_emisor', 'razon_social', 'giro', 'comuna_ciudad', 'proveedor_dte', 'dte_certificado']);
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['rut', 'rut_dv']);
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn(['rut']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['dte_tipo', 'dte_folio', 'dte_token', 'dte_pdf_url', 'dte_estado']);
        });
    }
};