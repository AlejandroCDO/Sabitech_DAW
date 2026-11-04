<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos ficticios de laboratorio. El orden respeta las claves foráneas.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UsuarioSeeder::class,
            UbicacionSeeder::class,
            PonenteSeeder::class,
            CharlaSeeder::class,
            ReservaSeeder::class,
        ]);
    }
}
