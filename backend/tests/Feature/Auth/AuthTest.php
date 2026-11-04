<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_correcto_devuelve_token_y_usuario(): void
    {
        $usuario = Usuario::factory()->create(['email' => 'demo@sabitech.test']);

        $respuesta = $this->postJson('/api/auth/login', [
            'email' => 'demo@sabitech.test',
            'password' => 'password',
        ]);

        $respuesta->assertOk()
            ->assertJsonStructure(['token', 'usuario' => ['id', 'email', 'rol']])
            ->assertJsonPath('usuario.id', $usuario->id)
            ->assertJsonPath('usuario.rol.nombre', 'asistente');
    }

    public function test_login_con_credenciales_incorrectas_devuelve_422(): void
    {
        Usuario::factory()->create(['email' => 'demo@sabitech.test']);

        $this->postJson('/api/auth/login', [
            'email' => 'demo@sabitech.test',
            'password' => 'incorrecta',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_me_sin_token_devuelve_401(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_me_con_token_devuelve_el_perfil(): void
    {
        Usuario::factory()->create(['email' => 'demo@sabitech.test']);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'demo@sabitech.test',
            'password' => 'password',
        ])->json('token');

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('email', 'demo@sabitech.test');
    }
}
