<?php

namespace Tests\Feature\Qr;

use App\Models\Charla;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QrTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function prepararReserva(): array
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 17:00:00'));

        $asistente = Usuario::factory()->asistente()->create();
        $acceso = Usuario::factory()->acceso()->create();

        $charla = Charla::factory()->create([
            'inicio' => now()->addMinutes(10),
            'fin' => now()->addHour(),
        ]);

        $reserva = Reserva::factory()->create([
            'usuario_id' => $asistente->id,
            'charla_id' => $charla->id,
        ]);

        return [$asistente, $acceso, $charla, $reserva];
    }

    private function obtenerToken(Usuario $asistente, Reserva $reserva): string
    {
        Sanctum::actingAs($asistente);

        return $this->getJson("/api/asistente/reservas/{$reserva->id}/qr")
            ->assertOk()
            ->json('token');
    }

    public function test_un_qr_valido_registra_la_asistencia(): void
    {
        [$asistente, $acceso, $charla, $reserva] = $this->prepararReserva();

        $token = $this->obtenerToken($asistente, $reserva);

        Sanctum::actingAs($acceso);

        $this->postJson('/api/acceso/asistencias/qr', [
            'qr_token' => $token,
            'charla_id' => $charla->id,
        ])->assertStatus(201);

        $this->assertDatabaseHas('asistencias', ['reserva_id' => $reserva->id]);
    }

    public function test_un_qr_caducado_devuelve_422(): void
    {
        [$asistente, $acceso, $charla, $reserva] = $this->prepararReserva();

        $token = $this->obtenerToken($asistente, $reserva);

        Carbon::setTestNow(now()->addSeconds(90));
        Sanctum::actingAs($acceso);

        $this->postJson('/api/acceso/asistencias/qr', [
            'qr_token' => $token,
            'charla_id' => $charla->id,
        ])->assertStatus(422);
    }

    public function test_un_qr_manipulado_devuelve_422(): void
    {
        [$asistente, $acceso, $charla, $reserva] = $this->prepararReserva();

        $token = $this->obtenerToken($asistente, $reserva);
        $token = substr($token, 0, -1).($token[-1] === 'a' ? 'b' : 'a');

        Sanctum::actingAs($acceso);

        $this->postJson('/api/acceso/asistencias/qr', [
            'qr_token' => $token,
            'charla_id' => $charla->id,
        ])->assertStatus(422);
    }

    public function test_un_qr_de_otra_charla_devuelve_422(): void
    {
        [$asistente, $acceso, , $reserva] = $this->prepararReserva();

        $otraCharla = Charla::factory()->create([
            'inicio' => now()->addMinutes(10),
            'fin' => now()->addHour(),
        ]);

        $token = $this->obtenerToken($asistente, $reserva);
        Sanctum::actingAs($acceso);

        $this->postJson('/api/acceso/asistencias/qr', [
            'qr_token' => $token,
            'charla_id' => $otraCharla->id,
        ])->assertStatus(422);
    }

    public function test_la_misma_reserva_no_se_puede_validar_dos_veces(): void
    {
        [$asistente, $acceso, $charla, $reserva] = $this->prepararReserva();

        $token = $this->obtenerToken($asistente, $reserva);
        Sanctum::actingAs($acceso);

        $datos = ['qr_token' => $token, 'charla_id' => $charla->id];

        $this->postJson('/api/acceso/asistencias/qr', $datos)->assertStatus(201);
        $this->postJson('/api/acceso/asistencias/qr', $datos)->assertStatus(409);
    }

    public function test_la_validacion_manual_tambien_registra_una_sola_asistencia(): void
    {
        [, $acceso, $charla, $reserva] = $this->prepararReserva();

        Sanctum::actingAs($acceso);

        $datos = ['reserva_id' => $reserva->id, 'charla_id' => $charla->id];

        $this->postJson('/api/acceso/asistencias/manual', $datos)->assertStatus(201);
        $this->postJson('/api/acceso/asistencias/manual', $datos)->assertStatus(409);
    }
}
