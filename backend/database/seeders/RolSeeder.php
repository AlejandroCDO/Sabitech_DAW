<?php

namespace Database\Seeders;

use App\Enums\NombreRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        foreach (NombreRol::cases() as $rol) {
            Rol::firstOrCreate(['nombre' => $rol->value]);
        }
    }
}
