<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        $ubicaciones = [
            ['Valencia', 'Valencia', 'Calle de la Paz', '46003', '10', null, 'Aula Magna'],
            ['Valencia', 'Paterna', 'Avenida del Conocimiento', '46980', '25', 'B', 'Sala Tecnológica'],
            ['Alicante', 'Alicante', 'Calle Mayor', '03002', '5', null, 'Auditorio Costa'],
        ];

        foreach ($ubicaciones as [$provincia, $municipio, $calle, $cp, $numero, $puerta, $nombre]) {
            Ubicacion::updateOrCreate(['nombre' => $nombre, 'municipio' => $municipio], [
                'provincia' => $provincia,
                'calle' => $calle,
                'codigo_postal' => $cp,
                'numero' => $numero,
                'puerta' => $puerta,
            ]);
        }
    }
}
