<?php

namespace Database\Factories;

use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ubicacion>
 */
class UbicacionFactory extends Factory
{
    protected $model = Ubicacion::class;

    public function definition(): array
    {
        return [
            'provincia' => fake()->randomElement(['Valencia', 'Alicante', 'Castellón']),
            'municipio' => fake()->city(),
            'calle' => fake()->streetName(),
            'codigo_postal' => fake()->numerify('46###'),
            'numero' => (string) fake()->numberBetween(1, 120),
            'puerta' => null,
            'nombre' => 'Aula '.fake()->numberBetween(1, 20),
        ];
    }
}
