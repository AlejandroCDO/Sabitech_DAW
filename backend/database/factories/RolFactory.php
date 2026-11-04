<?php

namespace Database\Factories;

use App\Enums\NombreRol;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rol>
 */
class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        return [
            'nombre' => NombreRol::Asistente->value,
        ];
    }

    public function admin(): static
    {
        return $this->state(['nombre' => NombreRol::Admin->value]);
    }

    public function acceso(): static
    {
        return $this->state(['nombre' => NombreRol::Acceso->value]);
    }

    public function asistente(): static
    {
        return $this->state(['nombre' => NombreRol::Asistente->value]);
    }
}
