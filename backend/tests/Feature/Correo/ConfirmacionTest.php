<?php

namespace Tests\Feature\Correo;

use App\Models\Charla;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConfirmacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_la_reserva_envia_correo_y_el_reenvio_espera_60_segundos(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 17:00:00'));

        $usuario = Usuario::factory()->asistente()->create();
        $charla = Charla::factory()->create([
            'inicio' => now()->addHour(),
            'fin' => now()->addHours(2),
        ]);

        Sanctum::actingAs($usuario);

        $reservaId = $this->postJson('/api/asistente/reservas', [
            'charla_id' => $charla->id,
        ])->assertStatus(201)->json('id');

        $reserva = Reserva::findOrFail($reservaId);

        $this->assertSame(1, $reserva->email_intentos);
        $this->assertNotNull($reserva->email_ultimo_envio);

        $this->postJson("/api/asistente/reservas/{$reservaId}/reenviar-confirmacion")
            ->assertStatus(429);

        Carbon::setTestNow(now()->addSeconds(61));

        $this->postJson("/api/asistente/reservas/{$reservaId}/reenviar-confirmacion")
            ->assertOk()
            ->assertJsonPath('email_intentos', 2);
    }
}
