<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp como identificación del cliente.
 * - Normaliza teléfonos existentes (solo dígitos).
 * - Hace únicos telefono/email/dni POR taller (tenant_id) en vez de globales.
 * - No toca datos: las filas con tenant_id NULL se asignan al primer tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Asegurar tenant en filas antiguas (los seed venían con tenant_id NULL)
        $primerTenant = \App\Models\Tenant::orderBy('id')->value('id');
        if ($primerTenant) {
            \App\Models\Cliente::withoutGlobalScopes()->whereNull('tenant_id')->update(['tenant_id' => $primerTenant]);
        }

        // 2) Normalizar teléfonos existentes: solo dígitos
        \App\Models\Cliente::withoutGlobalScopes()->chunkById(500, function ($clientes) {
            foreach ($clientes as $cliente) {
                $tel = preg_replace('/\D/', '', (string) $cliente->telefono);
                $cel = $cliente->celular ? preg_replace('/\D/', '', (string) $cliente->celular) : $cliente->celular;
                $dni = $cliente->dni !== null ? trim((string) $cliente->dni) : null;
                if ($dni === '') {
                    $dni = null;
                }
                if ($tel !== $cliente->telefono || $cel !== $cliente->celular || $dni !== $cliente->dni) {
                    $cliente->telefono = $tel;
                    $cliente->celular = $cel;
                    $cliente->dni = $dni;
                    $cliente->saveQuietly();
                }
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            // Quitar únicos globales (impiden repetir email/dni entre talleres distintos)
            try {
                $table->dropUnique(['email']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique(['dni']);
            } catch (\Throwable $e) {
            }

            // Únicos por taller: el WhatsApp es la identificación del cliente
            try {
                $table->unique(['tenant_id', 'telefono'], 'clientes_tenant_telefono_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->unique(['tenant_id', 'email'], 'clientes_tenant_email_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->unique(['tenant_id', 'dni'], 'clientes_tenant_dni_unique');
            } catch (\Throwable $e) {
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            try {
                $table->dropUnique('clientes_tenant_telefono_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique('clientes_tenant_email_unique');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropUnique('clientes_tenant_dni_unique');
            } catch (\Throwable $e) {
            }
        });
    }
};
