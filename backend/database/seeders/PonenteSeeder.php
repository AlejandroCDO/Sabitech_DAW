<?php

namespace Database\Seeders;

use App\Models\Ponente;
use Illuminate\Database\Seeder;

class PonenteSeeder extends Seeder
{
    public function run(): void
    {
        $ponentes = [
            ['Laura Ficticia', 'Inteligencia artificial', 'Ponente ficticia con experiencia en aprendizaje automático.'],
            ['Mario Ejemplo', 'Ciberseguridad', 'Ponente ficticio especializado en seguridad de aplicaciones.'],
            ['Carmen Demo', 'Cloud', 'Ponente ficticia dedicada a infraestructura en la nube.'],
        ];

        foreach ($ponentes as [$nombre, $especialidad, $biografia]) {
            Ponente::updateOrCreate(['nombre' => $nombre], [
                'especialidad' => $especialidad,
                'biografia' => $biografia,
            ]);
        }
    }
}
