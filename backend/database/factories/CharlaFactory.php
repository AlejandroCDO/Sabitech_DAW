<?php

namespace Database\Factories;

use App\Models\Charla;
use App\Models\Ponente;
use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Por defecto una charla futura, activa y sin plazas ocupadas.
 *
 * @extends Factory<Charla>
 */
class CharlaFactory extends Factory
{
    protected $model = Charla::class;

    public function definition(): array
    {
        $inicio = now()->addDays(fake()->numberBetween(3, 30))->setTime(fake()->numberBetween(9, 18), 0);

        return [
            'ponente_id' => Ponente::factory(),
            'ubicacion_id' => Ubicacion::factory(),
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'inicio' => $inicio,
            'fin' => $inicio->copy()->addHour(),
            'aforo' => 50,
            'plazas_ocupadas' => 0,
            'cancelada' => false,
        ];
    }

    public function cancelada(): static
    {
        return $this->state(['cancelada' => true]);
    }

    public function conAforo(int $aforo, int $ocupadas = 0): static
    {
        return $this->state(['aforo' => $aforo, 'plazas_ocupadas' => $ocupadas]);
    }
}
