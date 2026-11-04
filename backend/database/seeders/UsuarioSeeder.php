<?php

namespace Database\Seeders;

use App\Enums\NombreRol;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/** Usuarios ficticios de laboratorio; contraseña "password". No usar en producción. */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['admin@sabitech.test', 'Administrador Demo', NombreRol::Admin, '600000001'],
            ['acceso@sabitech.test', 'Personal de Acceso Demo', NombreRol::Acceso, '600000002'],
            ['asistente@sabitech.test', 'Asistente Demo', NombreRol::Asistente, '600000003'],
            ['asistente2@sabitech.test', 'Asistente Dos Demo', NombreRol::Asistente, '600000004'],
        ];

        foreach ($usuarios as [$email, $nombre, $rol, $telefono]) {
            Usuario::updateOrCreate(['email' => $email], [
                'rol_id' => Rol::where('nombre', $rol->value)->value('id'),
                'nombre' => $nombre,
                'telefono' => $telefono,
                'fecha_nacimiento' => '1995-01-15',
                'password' => 'password',
            ]);
        }
    }
}
