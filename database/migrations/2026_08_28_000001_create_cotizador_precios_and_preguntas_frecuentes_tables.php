<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizador_precios', function (Blueprint $table) {
            $table->id();
            $table->string('servicio');
            $table->string('servicio_label');
            $table->string('dispositivo');
            $table->string('dispositivo_label');
            $table->unsignedInteger('precio_min');
            $table->unsignedInteger('precio_max');
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('preguntas_frecuentes', function (Blueprint $table) {
            $table->id();
            $table->string('pregunta');
            $table->text('respuesta');
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── Datos iniciales del cotizador (los mismos que estaban en el código) ──
        $dispositivos = [
            'celular'  => 'Celular / Smartphone',
            'tablet'   => 'Tablet',
            'notebook' => 'Notebook',
            'pc'       => 'PC de Escritorio',
            'consola'  => 'Consola',
        ];
        $servicios = [
            'pantalla'      => ['label' => 'Cambio de Pantalla', 'precios' => ['celular' => [45000, 90000], 'tablet' => [60000, 120000], 'notebook' => [90000, 180000], 'pc' => [70000, 150000], 'consola' => [90000, 180000]]],
            'bateria'       => ['label' => 'Cambio de Batería', 'precios' => ['celular' => [25000, 45000], 'tablet' => [35000, 60000], 'notebook' => [45000, 90000], 'consola' => [40000, 70000]]],
            'puerto'        => ['label' => 'Puerto de Carga', 'precios' => ['celular' => [20000, 40000], 'tablet' => [25000, 45000], 'notebook' => [30000, 60000], 'pc' => [15000, 35000], 'consola' => [30000, 60000]]],
            'mantenimiento' => ['label' => 'Mantenimiento / Limpieza', 'precios' => ['celular' => [15000, 25000], 'tablet' => [18000, 30000], 'notebook' => [25000, 45000], 'pc' => [20000, 40000], 'consola' => [25000, 40000]]],
            'software'      => ['label' => 'Software / Sistema', 'precios' => ['celular' => [15000, 30000], 'tablet' => [15000, 30000], 'notebook' => [20000, 40000], 'pc' => [20000, 40000], 'consola' => [20000, 35000]]],
            'placa'         => ['label' => 'Reparación de Placa', 'precios' => ['celular' => [60000, 150000], 'tablet' => [70000, 160000], 'notebook' => [80000, 180000], 'pc' => [60000, 150000], 'consola' => [80000, 200000]]],
            'camara'        => ['label' => 'Cámara / Audio', 'precios' => ['celular' => [20000, 50000], 'tablet' => [25000, 55000], 'notebook' => [30000, 60000], 'pc' => [20000, 45000]]],
            'datos'         => ['label' => 'Recuperación de Datos', 'precios' => ['celular' => [30000, 80000], 'tablet' => [35000, 90000], 'notebook' => [40000, 120000], 'pc' => [30000, 90000]]],
        ];

        $orden = 0;
        $rows = [];
        foreach ($servicios as $clave => $info) {
            foreach ($dispositivos as $dClave => $dLabel) {
                if (!isset($info['precios'][$dClave])) {
                    continue;
                }
                [$min, $max] = $info['precios'][$dClave];
                $rows[] = [
                    'servicio'          => $clave,
                    'servicio_label'    => $info['label'],
                    'dispositivo'       => $dClave,
                    'dispositivo_label' => $dLabel,
                    'precio_min'        => $min,
                    'precio_max'        => $max,
                    'orden'             => $orden++,
                    'activo'            => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
        }
        DB::table('cotizador_precios')->insert($rows);

        // ── FAQs iniciales (las mismas que estaban en el código) ──
        DB::table('preguntas_frecuentes')->insert([
            ['pregunta' => '¿Cuánto tarda una reparación?', 'respuesta' => 'La mayoría de las reparaciones (pantallas, baterías, puertos de carga) están listas en 24 a 72 horas. Los casos de placa o repuestos importados pueden demorar más, y te avisamos por WhatsApp en cuanto tengamos novedades.', 'orden' => 0, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pregunta' => '¿El diagnóstico tiene costo?', 'respuesta' => 'No. El diagnóstico es sin costo y te entregamos el presupuesto antes de tocar tu equipo. Solo continuamos con la reparación si tú la apruebas.', 'orden' => 1, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pregunta' => '¿Qué garantía tienen las reparaciones?', 'respuesta' => 'Todas nuestras reparaciones incluyen 3 meses de garantía escrita, tanto en repuestos como en mano de obra.', 'orden' => 2, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pregunta' => '¿Necesito la boleta para retirar mi equipo?', 'respuesta' => 'Presenta tu boleta o el código de la orden (RPT-XXXXXX). Sin comprobante no entregamos el equipo, por la seguridad de tus datos y tu inversión.', 'orden' => 3, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['pregunta' => '¿Pierdo mis datos con la reparación?', 'respuesta' => 'En reparaciones de hardware (pantalla, batería, puerto de carga) tus datos no se tocan. En servicios de software hacemos respaldo previo siempre que el equipo lo permita.', 'orden' => 4, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizador_precios');
        Schema::dropIfExists('preguntas_frecuentes');
    }
};