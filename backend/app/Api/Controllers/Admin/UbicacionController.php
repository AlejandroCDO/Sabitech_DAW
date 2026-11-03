<?php

namespace App\Api\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->integer('per_page', 15), 100);

        $ubicaciones = Ubicacion::query()
            ->when($request->filled('provincia'), fn ($q) => $q->where('provincia', $request->string('provincia')))
            ->when($request->filled('municipio'), fn ($q) => $q->where('municipio', $request->string('municipio')))
            ->orderBy('provincia')
            ->orderBy('municipio')
            ->paginate($perPage);

        return response()->json($ubicaciones);
    }

    public function show(Ubicacion $ubicacion): JsonResponse
    {
        return response()->json($ubicacion);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'        => ['required', 'string', 'max:50'],
            'provincia'     => ['required', 'string', 'max:100'],
            'municipio'     => ['required', 'string', 'max:100'],
            'calle'         => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'max:10'],
            'numero'        => ['nullable', 'string', 'max:10'],
            'puerta'        => ['nullable', 'string', 'max:10'],
        ]);

        return response()->json(Ubicacion::create($data), 201);
    }

    public function update(Request $request, Ubicacion $ubicacion): JsonResponse
    {
        $data = $request->validate([
            'nombre'        => ['sometimes', 'string', 'max:50'],
            'provincia'     => ['sometimes', 'string', 'max:100'],
            'municipio'     => ['sometimes', 'string', 'max:100'],
            'calle'         => ['sometimes', 'string', 'max:100'],
            'codigo_postal' => ['sometimes', 'string', 'max:10'],
            'numero'        => ['nullable', 'string', 'max:10'],
            'puerta'        => ['nullable', 'string', 'max:10'],
        ]);

        $ubicacion->update($data);

        return response()->json($ubicacion);
    }

    public function destroy(Ubicacion $ubicacion): JsonResponse
    {
        if ($ubicacion->charlas()->exists()) {
            return response()->json([
                'message' => 'La ubicación tiene charlas asociadas y no se puede eliminar.',
            ], 409);
        }

        $ubicacion->delete();

        return response()->json(null, 204);
    }
}