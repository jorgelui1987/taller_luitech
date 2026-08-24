<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;
use RuntimeException;

/**
 * Servicio centralizado de backup y restauración.
 *
 * Correcciones clave respecto a la implementación anterior:
 * - El divisor de sentencias es un tokenizador real: ignora comentarios (-- y bloque)
 *   pero NO descarta las sentencias que los siguen (antes, los "DROP TABLE" precedidos
 *   de comentarios se saltaban y nunca se ejecutaban).
 * - No corta sentencias dentro de strings (soporta ';' y saltos de línea en los datos,
 *   comillas escapadas '' y barras invertidas según el motor).
 * - Los errores de ejecución se registran y reportan; ya no se silencian.
 * - Los INSERT incluyen la lista explícita de columnas (tolera tablas con columnas
 *   añadidas después del backup).
 */
class BackupService
{
    private const TABLAS_EXCLUIDAS    = ['migrations', 'personal_access_tokens'];
    private const FILAS_POR_INSERT    = 200;
    private const MAX_ERRORES_REPORTE = 5;
    private const MAX_LOG_SENTENCIA   = 300;

    private const SQL_PGSQL_REPLICA = 'SET session_replication_role = replica';
    private const SQL_PGSQL_DEFAULT = 'SET session_replication_role = DEFAULT';
    private const SQL_MYSQL_FK_OFF  = 'SET FOREIGN_KEY_CHECKS=0';
    private const SQL_MYSQL_FK_ON   = 'SET FOREIGN_KEY_CHECKS=1';

    /** Comandos que nunca deben ejecutarse durante una restauración. */
    private const COMANDOS_BLOQUEADOS = [
        'DROP DATABASE', 'DROP SCHEMA', 'DROP USER',
        'CREATE DATABASE', 'CREATE USER', 'ALTER USER',
        'GRANT', 'REVOKE',
    ];

    /* ══════════════════════════ GENERACIÓN ══════════════════════════ */

    /**
     * Genera el contenido SQL completo de la base de datos actual.
     */
    public function generarSQL(): string
    {
        $driver = DB::connection()->getDriverName();
        $pdo    = DB::connection()->getPdo();
        $dbName = config('database.connections.' . $driver . '.database') ?: 'database';
        $ahora  = now()->format('d/m/Y H:i:s');

        $sql  = "-- ==============================================================\n";
        $sql .= "--  CRM Tienda Celulares — Backup Completo\n";
        $sql .= "--  Generado  : {$ahora}\n";
        $sql .= "--  Base datos: {$dbName}\n";
        $sql .= "--  Motor     : {$driver}\n";
        $sql .= "-- ==============================================================\n\n";

        $sql .= $driver === 'pgsql'
            ? self::SQL_PGSQL_REPLICA . ";\n\n"
            : "SET NAMES utf8mb4;\n" . self::SQL_MYSQL_FK_OFF . ";\n\n";

        // PostgreSQL no tiene la función DATABASE() (es de MySQL)
        $queryTablas = $driver === 'pgsql'
            ? "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
            : "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()";

        $tablas = array_map(
            fn (array $fila) => $fila[0],
            $pdo->query($queryTablas)->fetchAll(PDO::FETCH_NUM)
        );

        foreach ($tablas as $tabla) {
            if (in_array($tabla, self::TABLAS_EXCLUIDAS, true)) {
                continue;
            }

            $t = $this->quoteIdent($tabla, $driver);

            $sql .= "-- --------------------------------------------------------------\n";
            $sql .= "-- Tabla: {$tabla}\n";
            $sql .= "-- --------------------------------------------------------------\n";

            // PostgreSQL verifica dependencias de FK al hacer DROP aunque las
            // triggers estén desactivadas: CASCADE elimina también las FKs de
            // otras tablas que apuntan a esta, sin importar el orden.
            $sql .= $driver === 'pgsql'
                ? "DROP TABLE IF EXISTS {$t} CASCADE;\n"
                : "DROP TABLE IF EXISTS {$t};\n";

            $sql .= $driver === 'pgsql'
                ? $this->generarCreatePostgres($pdo, $tabla)
                : $this->generarCreateMysql($pdo, $tabla);

            $sql .= $this->generarDatosTabla($pdo, $driver, $tabla);
        }

        $sql .= $driver === 'pgsql'
            ? self::SQL_PGSQL_DEFAULT . ";\n"
            : self::SQL_MYSQL_FK_ON . ";\n";

        return $sql;
    }

    private function generarCreateMysql(PDO $pdo, string $tabla): string
    {
        $create = $pdo->query('SHOW CREATE TABLE `' . str_replace('`', '``', $tabla) . '`')
            ->fetch(PDO::FETCH_ASSOC);

        return $create['Create Table'] . ";\n\n";
    }

    /**
     * Genera el CREATE TABLE para PostgreSQL incluyendo clave primaria y
     * columnas serial (autoincrementales), información que se perdía antes.
     */
    private function generarCreatePostgres(PDO $pdo, string $tabla): string
    {
        $columnas = $pdo->query(
            "SELECT column_name, data_type, is_nullable, column_default
             FROM information_schema.columns
             WHERE table_name = '{$tabla}' AND table_schema = 'public'
             ORDER BY ordinal_position"
        )->fetchAll(PDO::FETCH_ASSOC);

        $lineas = [];
        foreach ($columnas as $col) {
            $default = $col['column_default'];

            // Columnas serial / identity (autoincrementales)
            if ($default !== null && preg_match('/^nextval\(/i', $default)) {
                $tipo = match ($col['data_type']) {
                    'integer'  => 'SERIAL',
                    'bigint'   => 'BIGSERIAL',
                    'smallint' => 'SMALLSERIAL',
                    default    => strtoupper($col['data_type']),
                };
                $lineas[] = "  \"{$col['column_name']}\" {$tipo}";
                continue;
            }

            $lineas[] = "  \"{$col['column_name']}\" " . strtoupper($col['data_type'])
                . ($col['is_nullable'] === 'NO' ? ' NOT NULL' : '')
                . ($default !== null ? ' DEFAULT ' . $default : '');
        }

        // Clave primaria (impide duplicados al restaurar)
        $pk = $pdo->query(
            "SELECT kcu.column_name
             FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
               ON tc.constraint_name = kcu.constraint_name
              AND tc.table_name      = kcu.table_name
             WHERE tc.constraint_type = 'PRIMARY KEY'
               AND tc.table_name = '{$tabla}'
               AND tc.table_schema = 'public'
             ORDER BY kcu.ordinal_position"
        )->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($pk)) {
            $colsPk   = implode(', ', array_map(fn ($c) => "\"{$c}\"", $pk));
            $lineas[] = "  PRIMARY KEY ({$colsPk})";
        }

        return "CREATE TABLE \"{$tabla}\" (\n" . implode(",\n", $lineas) . "\n);\n\n";
    }

    /**
     * Genera los INSERT de una tabla con lista de columnas explícita.
     */
    private function generarDatosTabla(PDO $pdo, string $driver, string $tabla): string
    {
        $rows = $pdo->query('SELECT * FROM ' . $this->quoteIdent($tabla, $driver))
            ->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return '';
        }

        // Lista de columnas explícita: tolera tablas destino con columnas
        // añadidas después de que se generó el backup.
        $listaCols = implode(', ', array_map(
            fn ($c) => $this->quoteIdent((string) $c, $driver),
            array_keys($rows[0])
        ));
        $t = $this->quoteIdent($tabla, $driver);

        $sql = '';
        foreach (array_chunk($rows, self::FILAS_POR_INSERT) as $chunk) {
            $sql .= "INSERT INTO {$t} ({$listaCols}) VALUES\n";
            $lines = [];
            foreach ($chunk as $row) {
                $vals = array_map(
                    fn ($v) => $v === null ? 'NULL'
                        // PostgreSQL devuelve las columnas boolean como PHP bool:
                        // (string) false === '' y PostgreSQL rechaza '' como boolean.
                        : (is_bool($v) ? ($v ? 'TRUE' : 'FALSE') : $pdo->quote((string) $v)),
                    array_values($row)
                );
                $lines[] = '(' . implode(', ', $vals) . ')';
            }
            $sql .= implode(",\n", $lines) . ";\n";
        }
        $sql .= "\n";

        return $sql;
    }

    private function quoteIdent(string $identificador, string $driver): string
    {
        return $driver === 'pgsql'
            ? '"' . str_replace('"', '""', $identificador) . '"'
            : '`' . str_replace('`', '``', $identificador) . '`';
    }

    /* ══════════════════════════ RESTAURACIÓN ══════════════════════════ */

    /**
     * Restaura un contenido SQL completo sobre la base de datos actual.
     *
     * @return array{total:int, ejecutadas:int, bloqueadas:int, fallidas:int, errores:string[]}
     */
    public function restaurarDesdeContenido(string $contenido): array
    {
        $driver = DB::connection()->getDriverName();
        $sentencias = $this->dividirSentencias($contenido);

        if (empty($sentencias)) {
            throw new RuntimeException('El archivo no contiene sentencias SQL ejecutables.');
        }

        $stats = [
            'total'      => count($sentencias),
            'ejecutadas' => 0,
            'bloqueadas' => 0,
            'fallidas'   => 0,
            'errores'    => [],
        ];

        $this->desactivarFK();

        try {
            foreach ($sentencias as $stmt) {
                // PostgreSQL: agregar CASCADE a los DROP TABLE que no lo traigan.
                // Los backups antiguos no lo incluyen y el DROP falla cuando otra
                // tabla tiene una FK hacia la tabla a eliminar.
                if ($driver === 'pgsql'
                    && preg_match('/^DROP TABLE IF EXISTS\s+(.+)$/i', $stmt, $m)
                    && !str_contains(strtoupper($stmt), 'CASCADE')) {
                    $stmt = 'DROP TABLE IF EXISTS ' . trim($m[1]) . ' CASCADE';
                }

                $upper = strtoupper($stmt);

                $bloqueada = false;
                foreach (self::COMANDOS_BLOQUEADOS as $cmd) {
                    if (str_contains($upper, $cmd)) {
                        $bloqueada = true;
                        break;
                    }
                }

                if ($bloqueada) {
                    $stats['bloqueadas']++;
                    Log::warning('Backup restore: sentencia bloqueada por seguridad', [
                        'sentencia' => mb_substr($stmt, 0, self::MAX_LOG_SENTENCIA),
                    ]);
                    continue;
                }

                try {
                    DB::unprepared($stmt);
                    $stats['ejecutadas']++;
                } catch (\Throwable $e) {
                    // Registrar y contar el error; NUNCA silenciarlo.
                    $stats['fallidas']++;
                    Log::error('Backup restore: sentencia fallida', [
                        'error'     => $e->getMessage(),
                        'sentencia' => mb_substr($stmt, 0, self::MAX_LOG_SENTENCIA),
                    ]);
                    if (count($stats['errores']) < self::MAX_ERRORES_REPORTE) {
                        $stats['errores'][] = mb_substr($e->getMessage(), 0, 160);
                    }
                }
            }

            // PostgreSQL: reajustar las secuencias de los IDs tras restaurar
            // filas con IDs explícitos. Sin esto, la secuencia sigue en 1 y el
            // próximo INSERT duplica la clave primaria.
            if ($driver === 'pgsql') {
                $tablas = DB::select(
                    "SELECT table_name FROM information_schema.tables
                     WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
                );
                foreach ($tablas as $t) {
                    $tabla = $t->table_name;
                    try {
                        if ($tabla !== 'migrations' && Schema::hasColumn($tabla, 'id')) {
                            DB::statement(
                                "SELECT setval(pg_get_serial_sequence('{$tabla}', 'id'), "
                                . "COALESCE((SELECT MAX(id) FROM \"{$tabla}\"), 0) + 1, false)"
                            );
                        }
                    } catch (\Throwable) {
                        // Tablas sin secuencia (sin serial id): omitir
                    }
                }
            }
        } finally {
            // Garantizar la reactivación de las claves foráneas siempre.
            $this->activarFK();
        }

        return $stats;
    }

    /**
     * Divide un archivo SQL en sentencias individuales de forma segura:
     * - Descarta comentarios de línea (doble guion) y de bloque (barra-asterisco),
     *   pero conserva la sentencia SQL que pueda estar junto a ellos.
     * - No corta dentro de strings entre comillas simples o dobles, aunque
     *   contengan punto y coma o saltos de línea.
     * - Soporta comillas escapadas ('' y "") y, solo en MySQL/MariaDB,
     *   escapes con barra invertida.
     *
     * @return string[]
     */
    public function dividirSentencias(string $contenido): array
    {
        $backslashEscapes = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);

        $sentencias = [];
        $buffer     = '';
        $len        = strlen($contenido);
        $i          = 0;

        while ($i < $len) {
            $char = $contenido[$i];
            $next = $i + 1 < $len ? $contenido[$i + 1] : '';

            // Comentario de línea: descartar hasta el salto de línea
            if ($char === '-' && $next === '-') {
                while ($i < $len && $contenido[$i] !== "\n") {
                    $i++;
                }
                $buffer .= ' ';
                continue;
            }

            // Comentario de bloque: descartar hasta el cierre del bloque
            if ($char === '/' && $next === '*') {
                $i += 2;
                while ($i + 1 < $len && !($contenido[$i] === '*' && $contenido[$i + 1] === '/')) {
                    $i++;
                }
                $i = min($i + 2, $len);
                $buffer .= ' ';
                continue;
            }

            // String entre comillas: copiarlo íntegro sin interpretar ';'
            // ni comentarios en su interior.
            if ($char === "'" || $char === '"') {
                $quote   = $char;
                $buffer .= $char;
                $i++;
                while ($i < $len) {
                    $c = $contenido[$i];
                    $n = $i + 1 < $len ? $contenido[$i + 1] : '';

                    if ($backslashEscapes && $c === '\\' && ($n === "'" || $n === '"' || $n === '\\')) {
                        $buffer .= $c . $n;
                        $i += 2;
                        continue;
                    }

                    if ($c === $quote) {
                        if ($n === $quote) { // comilla escapada ('' o "")
                            $buffer .= $c . $n;
                            $i += 2;
                            continue;
                        }
                        $buffer .= $c; // cierra el string
                        $i++;
                        break;
                    }

                    $buffer .= $c;
                    $i++;
                }
                continue;
            }

            // Fin de sentencia
            if ($char === ';') {
                $stmt = trim($buffer);
                if ($stmt !== '') {
                    $sentencias[] = $stmt;
                }
                $buffer = '';
                $i++;
                continue;
            }

            $buffer .= $char;
            $i++;
        }

        $resto = trim($buffer);
        if ($resto !== '') {
            $sentencias[] = $resto;
        }

        return $sentencias;
    }

    /**
     * Valida que el contenido parezca un backup generado por el sistema.
     */
    public function validarContenidoBackup(string $contenido): void
    {
        $sentenciasPermitidas = [
            'INSERT INTO', 'CREATE TABLE', 'SET NAMES', 'SET FOREIGN_KEY_CHECKS',
            'SET session_replication_role', 'DROP TABLE IF EXISTS', 'ALTER TABLE',
            'UPDATE ', 'DELETE FROM',
        ];

        foreach ($sentenciasPermitidas as $permitida) {
            if (stripos($contenido, $permitida) !== false) {
                return;
            }
        }

        throw new RuntimeException('El archivo no contiene sentencias SQL válidas de backup.');
    }

    private function desactivarFK(): void
    {
        try {
            match (DB::connection()->getDriverName()) {
                'pgsql'            => DB::statement(self::SQL_PGSQL_REPLICA),
                'mysql', 'mariadb' => DB::statement(self::SQL_MYSQL_FK_OFF),
                default            => null, // SQLite y otros motores no lo requieren
            };
        } catch (\Throwable) {
            // Algunos entornos restringen estas sentencias; continuar igualmente.
        }
    }

    private function activarFK(): void
    {
        try {
            match (DB::connection()->getDriverName()) {
                'pgsql'            => DB::statement(self::SQL_PGSQL_DEFAULT),
                'mysql', 'mariadb' => DB::statement(self::SQL_MYSQL_FK_ON),
                default            => null,
            };
        } catch (\Throwable) {
        }
    }
}