<?php

namespace Database\Seeders;

use App\Enums\EstadoReserva;
use App\Models\Charla;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $reservas = [
            // [email, título de la charla, estado]
            ['asistente@sabitech.test', 'Introducción a la inteligencia artificial', EstadoReserva::Confirmada],
            ['asistente@sabitech.test', 'Seguridad en aplicaciones web', EstadoReserva::Confirmada],
            ['asistente@sabitech.test', 'Despliegue en la nube', EstadoReserva::Cancelada],
            ['asistente2@sabitech.test', 'Taller de aforo completo', EstadoReserva::Confirmada],
        ];

        foreach ($reservas as [$email, $titulo, $estado]) {
            $usuarioId = Usuario::where('email', $email)->value('id');
            $charlaId = Charla::where('titulo', $titulo)->value('id');

            $existe = Reserva::where('usuario_id', $usuarioId)->where('charla_id', $charlaId)->exists();

            if (! $existe) {
                Reserva::create([
                    'usuario_id' => $usuarioId,
                    'charla_id' => $charlaId,
                    'estado' => $estado,
                    'qr_secret' => bin2hex(random_bytes(32)),
                ]);
            }
        }

        // Contador coherente con las reservas activas (no canceladas).
        foreach (Charla::all() as $charla) {
            $charla->update([
                'plazas_ocupadas' => $charla->reservas()->activas()->count(),
            ]);
        }
    }
}
