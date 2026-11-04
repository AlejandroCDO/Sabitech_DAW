<?php

namespace App\Api\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Charla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Asistencias registradas de una charla.
 * La charla se obtiene a través de la reserva (asistencias no tiene charla_id).
 */
class AsistenciaCharlaController extends Controller
{
    public function index(Request $request, Charla $charla): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 20), 100));

        $asistencias = Asistencia::query()
            ->whereHas('reserva', fn ($q) => $q->where('charla_id', $charla->id))
            ->with(['reserva.usuario:id,nombre,email', 'validador:id,nombre'])
            ->latest('validada_en')
            ->paginate($perPage);

        $reservasActivas = $charla->reservas()->activas()->count();
        $asistentes      = Asistencia::whereHas('reserva', fn ($q) => $q->where('charla_id', $charla->id)->activas())->count();

        return response()->json([
            'charla' => $charla->only(['id', 'titulo', 'aforo']),
            'resumen' => [
                'reservas_activas' => $reservasActivas,
                'asistentes'       => $asistentes,
                'no_asistentes'    => max($reservasActivas - $asistentes, 0),
            ],
            'asistencias' => $asistencias,
        ]);
    }
}