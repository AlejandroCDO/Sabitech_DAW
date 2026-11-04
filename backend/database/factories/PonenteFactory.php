<?php

namespace Database\Factories;

use App\Models\Ponente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ponente>
 */
class PonenteFactory extends Factory
{
    protected $model = Ponente::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'biografia' => fake()->paragraph(),
            'foto_url' => null,
            'especialidad' => fake()->randomElement(['Ciberseguridad', 'Cloud', 'Inteligencia artificial', 'Desarrollo web']),
            'linkedin_url' => null,
        ];
    }
}
