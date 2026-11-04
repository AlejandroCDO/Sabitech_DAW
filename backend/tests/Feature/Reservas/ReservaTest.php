<?php

namespace Tests\Feature\Reservas;

use App\Models\Charla;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function reservar(Usuario $usuario, Charla $charla)
    {
        Sanctum::actingAs($usuario);

        return $this->postJson('/api/asistente/reservas', ['charla_id' => $charla->id]);
    }

    public function test_reserva_valida_se_crea_confirmada_e_incrementa_plazas(): void
    {
        $usuario = Usuario::factory()->create();
        $charla = Charla::factory()->conAforo(5)->create();

        $this->reservar($usuario, $charla)
            ->assertStatus(201)
            ->assertJsonPath('estado', 'confirmada');

        $this->assertDatabaseHas('reservas', [
            'usuario_id' => $usuario->id,
            'charla_id' => $charla->id,
            'estado' => 'confirmada',
        ]);
        $this->assertSame(1, $charla->fresh()->plazas_ocupadas);
    }

    public function test_reserva_repetida_devuelve_409_sin_duplicar_ni_sumar_plazas(): void
    {
        $usuario = Usuario::factory()->create();
        $charla = Charla::factory()->conAforo(5)->create();

        $this->reservar($usuario, $charla)->assertStatus(201);
        $this->reservar($usuario, $charla)->assertStatus(409);

        $this->assertSame(1, Reserva::where('usuario_id', $usuario->id)->count());
        $this->assertSame(1, $charla->fresh()->plazas_ocupadas);
    }

    public function test_reserva_sin_plazas_devuelve_409(): void
    {
        $usuario = Usuario::factory()->create();
        $charla = Charla::factory()->conAforo(1, 1)->create();

        $this->reservar($usuario, $charla)->assertStatus(409);

        $this->assertSame(0, Reserva::count());
        $this->assertSame(1, $charla->fresh()->plazas_ocupadas);
    }

    public function test_la_reserva_persiste_y_aparece_en_mis_reservas(): void
    {
        $usuario = Usuario::factory()->create();
        $charla = Charla::factory()->create();

        $this->reservar($usuario, $charla)->assertStatus(201);

        $this->getJson('/api/asistente/reservas')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.charla_id', $charla->id);
    }

    public function test_cancelar_y_volver_a_reservar_no_supera_el_aforo(): void
    {
        $a = Usuario::factory()->create();
        $b = Usuario::factory()->create();
        $charla = Charla::factory()->conAforo(1)->create();

        $reservaId = $this->reservar($a, $charla)->assertStatus(201)->json('id');
        $this->assertSame(1, $charla->fresh()->plazas_ocupadas);

        $this->deleteJson("/api/asistente/reservas/{$reservaId}")->assertOk();
        $this->assertSame(0, $charla->fresh()->plazas_ocupadas);

        $this->reservar($a, $charla)->assertStatus(201);
        $this->assertSame(1, $charla->fresh()->plazas_ocupadas);

        $this->reservar($b, $charla)->assertStatus(409);

        $this->assertSame(1, $charla->fresh()->plazas_ocupadas);
        $this->assertSame(1, Reserva::activas()->where('charla_id', $charla->id)->count());
    }
}
