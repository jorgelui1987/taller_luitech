<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Reparacion;
use Carbon\Carbon;

class BackupController extends Controller
{
    private const SQL_PGSQL_REPLICA = 'SET session_replication_role = replica';
    private const SQL_PGSQL_DEFAULT = 'SET session_replication_role = DEFAULT';
    private const SQL_MYSQL_FK_OFF = 'SET FOREIGN_KEY_CHECKS=0';
    private const SQL_MYSQL_FK_ON = 'SET FOREIGN_KEY_CHECKS=1';

    private string $backupDir;

    public function __construct(private BackupService $backupService)
    {
        $this->backupDir = storage_path('app/backups');
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    // ── Vista principal ──────────────────────────────────────────────────────
    public function index()
    {
        $archivos = glob($this->backupDir . '/*.sql') ?: [];
        $backups  = [];

        foreach ($archivos as $archivo) {
            $backups[] = [
                'nombre'  => basename($archivo),
                'tamanio' => filesize($archivo),
                'fecha'   => Carbon::createFromTimestamp(filemtime($archivo)),
            ];
        }

        usort($backups, fn($a, $b) => $b['fecha'] <=> $a['fecha']);

        $stats = [
            'ventas'      => Venta::count(),
            'clientes'    => Cliente::count(),
            'productos'   => Producto::count(),
            'reparaciones'=> Reparacion::count(),
            'backups'     => count($backups),
            'ultimo'      => empty($backups) ? null : $backups[0]['fecha'],
            'tamTotal'    => array_sum(array_column($backups, 'tamanio')),
        ];

        return view('backup.index', compact('backups', 'stats'));
    }

    // ── Crear backup ─────────────────────────────────────────────────────────
    public function crear()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $sql    = $this->backupService->generarSQL();
            $nombre = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
            $ruta   = $this->backupDir . '/' . $nombre;
            file_put_contents($ruta, $sql);

            return back()->with('success', "Backup <strong>{$nombre}</strong> creado correctamente (" . $this->formatBytes(filesize($ruta)) . ").");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al crear el backup: ' . e($e->getMessage()));
        }
    }

    // ── Descargar archivo ────────────────────────────────────────────────────
    public function descargar(string $nombre)
    {
        $ruta = $this->backupDir . '/' . basename($nombre);

        abort_unless(file_exists($ruta) && str_ends_with($nombre, '.sql'), 404, 'Archivo no encontrado.');

        return response()->download($ruta);
    }

    // ── Eliminar archivo ─────────────────────────────────────────────────────
    public function eliminar(string $nombre)
    {
        $ruta = $this->backupDir . '/' . basename($nombre);

        if (file_exists($ruta) && str_ends_with($nombre, '.sql')) {
            unlink($ruta);
            return back()->with('success', "Backup <strong>{$nombre}</strong> eliminado.");
        }

        return back()->with('error', 'Archivo no encontrado.');
    }

    // ── Restaurar desde archivo ───────────────────────────────────────────────
    public function restaurar(Request $request)
    {
        $request->validate([
            'archivo_sql' => 'required|file|max:51200|mimes:sql,txt', // NOSONAR - Límite de 50MB para restauración de backups
        ], [
            'archivo_sql.required' => 'Debes seleccionar un archivo .sql',
            'archivo_sql.max'      => 'El archivo no puede superar 100 MB.',
            'archivo_sql.mimes'    => 'El archivo debe ser de tipo .sql',
        ]);

        set_time_limit(600);
        ini_set('memory_limit', '512M');

        try {
            // Backup automático de seguridad antes de restaurar
            $autoNombre = 'pre_restore_' . now()->format('Y-m-d_H-i-s') . '.sql';
            file_put_contents($this->backupDir . '/' . $autoNombre, $this->backupService->generarSQL());

            $contenido = file_get_contents($request->file('archivo_sql')->getRealPath());

            $this->backupService->validarContenidoBackup($contenido);

            // El servicio se encarga de dividir sentencias, desactivar FK,
            // ejecutar todo y reportar los errores reales.
            $stats = $this->backupService->restaurarDesdeContenido($contenido);

            $resumen = "Sentencias ejecutadas: <strong>{$stats['ejecutadas']}</strong> de {$stats['total']}"
                . ($stats['bloqueadas'] > 0 ? " · Bloqueadas por seguridad: {$stats['bloqueadas']}" : '');

            if ($stats['fallidas'] === 0) {
                return back()->with('success',
                    "Base de datos restaurada correctamente. {$resumen}."
                    . " Se guardó un backup automático previo (<strong>{$autoNombre}</strong>).");
            }

            // Restauración parcial: informar con detalle en lugar de silenciar errores
            $listaErrores = implode('<br>• ', array_map('e', $stats['errores']));
            $extra = $stats['fallidas'] > count($stats['errores'])
                ? '<br>… y ' . ($stats['fallidas'] - count($stats['errores'])) . ' errores más (detalle completo en storage/logs/laravel.log).'
                : '';

            return back()->with('warning',
                "Restauración <strong>parcial</strong>: {$stats['fallidas']} de {$stats['total']} sentencias fallaron y fueron omitidas."
                . "<br>{$resumen}."
                . '<br><strong>Primeros errores detectados:</strong><br>• ' . $listaErrores . $extra
                . "<br><br>Se conservó un backup automático previo (<strong>{$autoNombre}</strong>) por si necesitas revertir.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al restaurar: ' . e($e->getMessage()));
        }
    }

    // ── Resetear sistema ─────────────────────────────────────────────────────
    public function resetear(Request $request)
    {
        $request->validate([
            'tipo_reset'   => 'required|in:ventas,datos,total',
            'confirmacion' => 'required|in:RESETEAR',
        ], [
            'confirmacion.in' => 'Debes escribir exactamente "RESETEAR" para confirmar.',
        ]);

        $driver = DB::connection()->getDriverName();

        // Backup automático antes de resetear
        try {
            $autoNombre = 'pre_reset_' . now()->format('Y-m-d_H-i-s') . '.sql';
            file_put_contents($this->backupDir . '/' . $autoNombre, $this->backupService->generarSQL());
        } catch (\Throwable) { /* Continúa aunque falle el backup */ }

        // Tablas hijas que referencian a las tablas padre (orden de eliminación correcto)
        $tablasHijas = [
            'reparacion_fotos',
            'detalle_ventas',
            'detalle_ordenes_compra',
            'ordenes_compra',
            'movimientos_stock',
            'comisiones_pagos',
            'gastos_fijos',
            'cupones',
            'resenas',
            'recordatorios_retiro',
            'devolucion_detalles',
            'devoluciones',
        ];

        try {
            $this->desactivarFK($driver);

            $msg = $this->ejecutarReset($request->tipo_reset, $tablasHijas);

            $this->activarFK($driver);
        } catch (\Throwable $e) {
            // Asegurar reactivación de FK en caso de error
            try {
                $this->activarFK($driver);
            } catch (\Throwable) {}

            return back()->with('error', 'Error durante el reset: ' . e($e->getMessage()));
        }

        return back()->with('success', $msg . ' Se generó un backup automático previo.');
    }

    private function ejecutarReset(string $tipo, array $tablasHijas): string
    {
        foreach ($tablasHijas as $tabla) {
            if (Schema::hasTable($tabla)) {
                DB::table($tabla)->delete();
            }
        }

        DB::table('ventas')->delete();
        DB::table('reparaciones')->delete();

        switch ($tipo) {
            case 'ventas':
                return 'Ventas y reparaciones eliminadas. Clientes, productos y usuarios conservados.';

            case 'datos':
                DB::table('clientes')->delete();
                DB::table('productos')->delete();
                return 'Datos comerciales eliminados. Usuarios, categorías y marcas conservados.';

            case 'total':
                DB::table('clientes')->delete();
                DB::table('productos')->delete();
                DB::table('users')->where('rol', '!=', 'admin')->delete();
                return 'Sistema reseteado a estado de fábrica. Solo el administrador fue conservado.';

            default:
                throw new \RuntimeException('Tipo de reset no válido.');
        }
    }

    private function desactivarFK(string $driver): void
    {
        if ($driver === 'pgsql') {
            DB::statement(self::SQL_PGSQL_REPLICA);
        } else {
            DB::statement(self::SQL_MYSQL_FK_OFF);
        }
    }

    private function activarFK(string $driver): void
    {
        if ($driver === 'pgsql') {
            DB::statement(self::SQL_PGSQL_DEFAULT);
        } else {
            DB::statement(self::SQL_MYSQL_FK_ON);
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }
        if ($bytes >= 1_024) {
            return round($bytes / 1_024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}