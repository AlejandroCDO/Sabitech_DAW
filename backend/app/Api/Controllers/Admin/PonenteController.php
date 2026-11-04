<?php

namespace App\Api\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ponente;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PonenteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 15), 100));

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
            'foto_url'     => ['nullable', 'url:http,https', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        return response()->json(Ponente::create($data)->refresh(), 201);
    }

    public function update(Request $request, Ponente $ponente): JsonResponse
    {
        $data = $request->validate([
            'nombre'       => ['sometimes', 'string', 'max:100'],
            'biografia'    => ['nullable', 'string'],
            'foto_url'     => ['nullable', 'url:http,https', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
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

        try {
            $ponente->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'El ponente tiene charlas asignadas y no se puede eliminar.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}