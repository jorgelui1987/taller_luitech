<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reajusta las secuencias (autoincrementales) de PostgreSQL al valor correcto.
 *
 * Necesario después de restaurar backups o ejecutar seeders que insertan filas
 * con IDs explícitos: en PostgreSQL esos INSERT no avanzan la secuencia, por lo
 * que el siguiente INSERT automático choca con "duplicate key value violates
 * unique constraint ..._pkey".
 */
class FixSequences extends Command
{
    protected $signature = 'db:fix-sequences';
    protected $description = 'Reajusta las secuencias de ID de todas las tablas (PostgreSQL)';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->info('Solo aplicable a PostgreSQL. Motor actual: ' . DB::connection()->getDriverName());

            return self::SUCCESS;
        }

        $tablas = DB::select(
            "SELECT DISTINCT c.table_name
             FROM information_schema.columns c
             JOIN information_schema.tables t
               ON t.table_name = c.table_name AND t.table_schema = c.table_schema
              AND t.table_type = 'BASE TABLE'
             WHERE c.column_name = 'id' AND c.table_schema = 'public'"
        );

        $corregidas = 0;
        foreach ($tablas as $t) {
            $tabla = $t->table_name;

            try {
                DB::statement(sprintf(
                    "SELECT setval(pg_get_serial_sequence('%s', 'id'), COALESCE((SELECT MAX(id) FROM %s), 0) + 1, false)",
                    $tabla,
                    $tabla
                ));
                $corregidas++;
                $this->line("  ✓ {$tabla}");
            } catch (\Throwable $e) {
                // Tablas sin secuencia asociada (sin serial id): omitir
                $this->line("  - {$tabla} (sin secuencia, omitida)");
            }
        }

        $this->info("Secuencias reajustadas en {$corregidas} tablas.");

        return self::SUCCESS;
    }
}