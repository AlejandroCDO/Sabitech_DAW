<?php

namespace Database\Factories;

use App\Enums\EstadoReserva;
use App\Models\Charla;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * No modifica charlas.plazas_ocupadas: quien la cree con una charla de aforo
 * limitado debe ajustar el contador.
 *
 * @extends Factory<Reserva>
 */
class ReservaFactory extends Factory
{
    protected $model = Reserva::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(),
            'charla_id' => Charla::factory(),
            'estado' => EstadoReserva::Confirmada,
            'qr_secret' => bin2hex(random_bytes(32)),
            'email_intentos' => 0,
            'email_ultimo_envio' => null,
        ];
    }

    public function cancelada(): static
    {
        return $this->state(['estado' => EstadoReserva::Cancelada]);
    }
}
