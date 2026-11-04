<?php

namespace Tests\Feature\Charlas;

use App\Models\Charla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_publico_lista_charlas_con_plazas_disponibles(): void
    {
        $charla = Charla::factory()->conAforo(10, 4)->create();

        $this->getJson('/api/charlas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $charla->id)
            ->assertJsonPath('data.0.plazas_disponibles', 6);
    }

    public function test_detalle_publico_de_una_charla(): void
    {
        $charla = Charla::factory()->conAforo(10, 10)->create();

        $this->getJson("/api/charlas/{$charla->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $charla->id)
            ->assertJsonPath('data.plazas_disponibles', 0)
            ->assertJsonStructure(['data' => ['ponente', 'ubicacion']]);
    }

    public function test_una_charla_cancelada_no_aparece_en_el_catalogo(): void
    {
        $visible = Charla::factory()->create();
        Charla::factory()->cancelada()->create();

        $this->getJson('/api/charlas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id);
    }
}
