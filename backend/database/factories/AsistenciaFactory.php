<?php

namespace Database\Factories;

use App\Models\Asistencia;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Asistencia manual por defecto (qr_token NULL).
 *
 * @extends Factory<Asistencia>
 */
class AsistenciaFactory extends Factory
{
    protected $model = Asistencia::class;

    public function definition(): array
    {
        return [
            'reserva_id' => Reserva::factory(),
            'validado_por' => Usuario::factory()->acceso(),
            'qr_token' => null,
            'validada_en' => now(),
        ];
    }
}
