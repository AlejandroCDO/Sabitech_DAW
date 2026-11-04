<?php

namespace App\Api\Controllers\Acceso;

use App\Api\DTOs\Acceso\ValidacionQrData;
use App\Api\Resources\Admin\AsistenciaAdminResource;
use App\Api\Services\Asistencias\AsistenciaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Registra la asistencia a partir del QR que muestra el asistente. */
class ValidacionQrController extends Controller
{
    public function __invoke(Request $request, AsistenciaService $servicio): JsonResponse
    {
        $datos = ValidacionQrData::fromArray($request->validate([
            'qr_token' => ['required', 'string', 'max:128'],
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]));

        $asistencia = $servicio->registrarPorQr($datos, $request->user());

        return (new AsistenciaAdminResource($asistencia))->response()->setStatusCode(201);
    }
}
