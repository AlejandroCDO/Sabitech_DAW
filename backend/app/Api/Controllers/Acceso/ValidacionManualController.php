<?php

namespace App\Api\Controllers\Acceso;

use App\Api\DTOs\Acceso\ValidacionManualData;
use App\Api\Resources\Admin\AsistenciaAdminResource;
use App\Api\Services\Asistencias\AsistenciaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Registra la asistencia sin QR, cuando el asistente no puede mostrar su código. */
class ValidacionManualController extends Controller
{
    public function __invoke(Request $request, AsistenciaService $servicio): JsonResponse
    {
        $datos = ValidacionManualData::fromArray($request->validate([
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]));

        $asistencia = $servicio->registrarManual($datos, $request->user());

        return (new AsistenciaAdminResource($asistencia))->response()->setStatusCode(201);
    }
}
