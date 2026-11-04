<?php

namespace Database\Factories;

use App\Enums\NombreRol;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Datos ficticios. Por defecto crea un asistente; el rol se reutiliza si ya
 * existe (roles.nombre es único). La contraseña "password" se cifra con el
 * cast "hashed" del modelo.
 *
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'rol_id' => fn () => Rol::firstOrCreate(['nombre' => NombreRol::Asistente->value])->id,
            'nombre' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => '6'.fake()->numerify('########'),
            'fecha_nacimiento' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'password' => 'password',
        ];
    }

    public function conRol(NombreRol $rol): static
    {
        return $this->state(fn () => [
            'rol_id' => Rol::firstOrCreate(['nombre' => $rol->value])->id,
        ]);
    }

    public function admin(): static
    {
        return $this->conRol(NombreRol::Admin);
    }

    public function acceso(): static
    {
        return $this->conRol(NombreRol::Acceso);
    }

    public function asistente(): static
    {
        return $this->conRol(NombreRol::Asistente);
    }
}
