<?php

namespace Tests\Feature\Admin;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccesoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_asistente_recibe_403_en_admin_charlas(): void
    {
        Sanctum::actingAs(Usuario::factory()->asistente()->create());

        $this->getJson('/api/admin/charlas')->assertStatus(403);
    }

    public function test_sin_token_recibe_401_en_admin_charlas(): void
    {
        $this->getJson('/api/admin/charlas')->assertStatus(401);
    }
}
