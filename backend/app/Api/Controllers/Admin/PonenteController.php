<?php

namespace App\Api\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ponente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PonenteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->integer('per_page', 15), 100);

        $ponentes = Ponente::query()
            ->when($request->filled('q'), fn ($q) => $q->where('nombre', 'like', '%' . $request->string('q') . '%'))
            ->orderBy('nombre')
            ->paginate($perPage);

        return response()->json($ponentes);
    }

    public function show(Ponente $ponente): JsonResponse
    {
        return response()->json($ponente);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:100'],
            'biografia'    => ['nullable', 'string'],
            'foto_url'     => ['nullable', 'url', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);

        return response()->json(Ponente::create($data), 201);
    }

    public function update(Request $request, Ponente $ponente): JsonResponse
    {
        $data = $request->validate([
            'nombre'       => ['sometimes', 'string', 'max:100'],
            'biografia'    => ['nullable', 'string'],
            'foto_url'     => ['nullable', 'url', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);

        $ponente->update($data);

        return response()->json($ponente);
    }

    public function destroy(Ponente $ponente): JsonResponse
    {
        if ($ponente->charlas()->exists()) {
            return response()->json([
                'message' => 'El ponente tiene charlas asignadas y no se puede eliminar.',
            ], 409);
        }

        $ponente->delete();

        return response()->json(null, 204);
    }
}