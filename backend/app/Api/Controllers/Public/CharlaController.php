<?php

namespace App\Api\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Charla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Catálogo público: no requiere sesión. */
class CharlaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'provincia' => ['nullable', 'string', 'max:100'],
        ]);

        $perPage = max(1, min($request->integer('per_page', 15), 100));
        $provincia = $datos['provincia'] ?? null;

        $charlas = Charla::query()
            ->with(['ponente', 'ubicacion'])
            ->where('cancelada', false)
            ->where('fin', '>=', now())
            ->when($provincia !== null && $provincia !== '', fn ($query) => $query
                ->whereHas('ubicacion', fn ($u) => $u->where('provincia', $provincia)))
            ->orderBy('inicio')
            ->paginate($perPage);

        return response()->json($charlas->through(fn (Charla $charla) => $this->formato($charla)));
    }

    public function show(Charla $charla): JsonResponse
    {
        $charla->load(['ponente', 'ubicacion']);

        return response()->json(['data' => $this->formato($charla)]);
    }

    private function formato(Charla $charla): array
    {
        return [
            'id' => $charla->id,
            'titulo' => $charla->titulo,
            'descripcion' => $charla->descripcion,
            'inicio' => $charla->inicio,
            'fin' => $charla->fin,
            'aforo' => $charla->aforo,
            'plazas_disponibles' => $charla->plazas_disponibles,
            'cancelada' => $charla->cancelada,
            'ponente' => $charla->ponente?->only(['id', 'nombre', 'biografia', 'foto_url', 'especialidad', 'linkedin_url']),
            'ubicacion' => $charla->ubicacion?->only(['id', 'nombre', 'provincia', 'municipio', 'calle', 'codigo_postal', 'numero', 'puerta']),
        ];
    }
}
