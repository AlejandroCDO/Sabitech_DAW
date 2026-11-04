<?php

namespace Database\Seeders;

use App\Models\Charla;
use App\Models\Ponente;
use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

/**
 * Charlas futuras relativas a now(). plazas_ocupadas se deja en 0 y
 * ReservaSeeder lo ajusta según las reservas activas.
 */
class CharlaSeeder extends Seeder
{
    public function run(): void
    {
        $charlas = [
            // [título, ponente, ubicación, días desde hoy, aforo, cancelada]
            ['Introducción a la inteligencia artificial', 'Laura Ficticia', 'Aula Magna', 7, 40, false],
            ['Seguridad en aplicaciones web', 'Mario Ejemplo', 'Auditorio Costa', 10, 60, false],
            ['Despliegue en la nube', 'Carmen Demo', 'Sala Tecnológica', 14, 30, false],
            ['Taller de aforo completo', 'Laura Ficticia', 'Aula Magna', 9, 1, false],
            ['Charla cancelada de ejemplo', 'Mario Ejemplo', 'Auditorio Costa', 12, 25, true],
        ];

        foreach ($charlas as [$titulo, $ponente, $ubicacion, $dias, $aforo, $cancelada]) {
            $inicio = now()->addDays($dias)->setTime(17, 0);

            Charla::updateOrCreate(['titulo' => $titulo], [
                'ponente_id' => Ponente::where('nombre', $ponente)->value('id'),
                'ubicacion_id' => Ubicacion::where('nombre', $ubicacion)->value('id'),
                'descripcion' => 'Charla ficticia de laboratorio: '.$titulo.'.',
                'inicio' => $inicio,
                'fin' => $inicio->copy()->addHours(2),
                'aforo' => $aforo,
                'plazas_ocupadas' => 0,
                'cancelada' => $cancelada,
            ]);
        }
    }
}
