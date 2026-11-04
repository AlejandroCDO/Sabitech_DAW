<?php

namespace App\Api\Controllers\Admin;

use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Charla;
use App\Models\Reserva;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CharlaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 15), 100));

        $charlas = Charla::with(['ponente:id,nombre', 'ubicacion:id,nombre,municipio'])
            ->when($request->filled('ponente_id'), fn ($q) => $q->where('ponente_id', $request->integer('ponente_id')))
            ->when($request->filled('ubicacion_id'), fn ($q) => $q->where('ubicacion_id', $request->integer('ubicacion_id')))
            ->when($request->boolean('solo_activas'), fn ($q) => $q->where('cancelada', false))
            ->orderBy('inicio')
            ->paginate($perPage);

        return response()->json($charlas);
    }

    public function show(Charla $charla): JsonResponse
    {
        return response()->json($charla->load(['ponente', 'ubicacion']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ponente_id'   => ['required', 'integer', 'exists:ponentes,id'],
            'ubicacion_id' => ['required', 'integer', 'exists:ubicaciones,id'],
            'titulo'       => ['required', 'string', 'max:100'],
            'descripcion'  => ['required', 'string'],
            'inicio'       => ['required', 'date'],
            'fin'          => ['required', 'date', 'after:inicio'],   // CHECK (fin > inicio)
            'aforo'        => ['required', 'integer', 'min:1', 'max:2147483647'],      // CHECK (aforo > 0)
        ]);

        $charla = Charla::create($data + [
            'plazas_ocupadas' => 0,
            'cancelada'       => false,
        ]);

        return response()->json($charla->load(['ponente', 'ubicacion']), 201);
    }

    public function update(Request $request, Charla $charla): JsonResponse
    {
        $data = $request->validate([
            'ponente_id'   => ['sometimes', 'integer', 'exists:ponentes,id'],
            'ubicacion_id' => ['sometimes', 'integer', 'exists:ubicaciones,id'],
            'titulo'       => ['sometimes', 'string', 'max:100'],
            'descripcion'  => ['sometimes', 'string'],
            'inicio'       => ['sometimes', 'date'],
            'fin'          => ['sometimes', 'date'],
            'aforo'        => ['sometimes', 'integer', 'min:1', 'max:2147483647'],
        ]);

        DB::transaction(function () use ($data, $charla) {
            // Bloqueamos la fila para que no entre una reserva mientras cambiamos el aforo
            $actual = Charla::lockForUpdate()->findOrFail($charla->id);

            $inicio = Carbon::parse($data['inicio'] ?? $actual->inicio);
            $fin    = Carbon::parse($data['fin'] ?? $actual->fin);

            if ($fin->lessThanOrEqualTo($inicio)) {
                throw ValidationException::withMessages([
                    'fin' => 'La fecha de fin debe ser posterior a la de inicio.',
                ]);
            }

            if (isset($data['aforo']) && $data['aforo'] < $actual->plazas_ocupadas) {
                throw ValidationException::withMessages([
                    'aforo' => "El aforo no puede ser menor que las plazas ya ocupadas ({$actual->plazas_ocupadas}).",
                ]);
            }

            $actual->update($data);
        });

        return response()->json($charla->fresh()->load(['ponente', 'ubicacion']));
    }

    /**
     * Cancela la charla y todas sus reservas activas.
     */
    public function cancelar(Charla $charla): JsonResponse
    {
        DB::transaction(function () use ($charla) {
            $actual = Charla::lockForUpdate()->findOrFail($charla->id);

            if ($actual->cancelada) {
                return;
            }

            Reserva::where('charla_id', $actual->id)
                ->activas()->update(['estado' => EstadoReserva::Cancelada->value]);

            $actual->update(['cancelada' => true, 'plazas_ocupadas' => 0]);
        });

        return response()->json($charla->fresh());
    }

    public function destroy(Charla $charla): JsonResponse
    {
        if ($charla->reservas()->exists()) {
            return response()->json([
                'message' => 'La charla tiene reservas. Cancélala en lugar de borrarla.',
            ], 409);
        }

        try {
            $charla->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'La charla tiene reservas. Cancélala en lugar de borrarla.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}