<?php

namespace App\Api\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Charla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

/**
 * Asistencias registradas de una charla.
 * La charla se obtiene a través de la reserva (asistencias no tiene charla_id).
 */
class AsistenciaCharlaController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/charlas/{charla}/asistencias',
        operationId: 'adminListarAsistenciasCharla',
        description: 'Asistencias registradas de una charla (se resuelven por su reserva), '
            .'ordenadas por la más reciente validada, con un resumen porcentual u discreto '
            .'de reservas activas, asistentes y ausentes.',
        tags: ['Administración'],
        parameters: [
            new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12),
            new OAT\QueryParameter(name: 'per_page', description: 'Elementos por página (entre 1 y 100, por defecto 20).', required: false, example: 20),
            new OAT\QueryParameter(name: 'page', description: 'Página a consultar.', required: false, example: 1),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Asistencias y resumen de la charla.', content: [
                new OAT\JsonContent(
                    type: 'object',
                    required: ['charla', 'resumen', 'asistencias'],
                    properties: [
                        new OAT\Property(property: 'charla', type: 'object', properties: [
                            new OAT\Property(property: 'id', type: 'integer', example: 12),
                            new OAT\Property(property: 'titulo', type: 'string'),
                            new OAT\Property(property: 'aforo', type: 'integer', example: 50),
                        ]),
                        new OAT\Property(property: 'resumen', type: 'object', properties: [
                            new OAT\Property(property: 'reservas_activas', description: 'Reservas no canceladas de la charla.', type: 'integer', example: 30),
                            new OAT\Property(property: 'asistentes', description: 'Asistencias registradas de reservas activas.', type: 'integer', example: 28),
                            new OAT\Property(property: 'no_asistentes', description: 'Ausentes: `reservas_activas - asistentes`, nunca negativo (nunca por debajo de 0).', type: 'integer', example: 2),
                        ]),
                        new OAT\Property(property: 'asistencias', type: 'object', description: 'Colección paginada de asistencias.', properties: [
                            new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: '#/components/schemas/AsistenciaAdmin')),
                            new OAT\Property(property: 'meta', ref: '#/components/schemas/MetaPaginacion'),
                            new OAT\Property(property: 'links', ref: '#/components/schemas/UrlPaginacion'),
                        ]),
                    ],
                ),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    public function index(Request $request, Charla $charla): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 20), 100));

        $asistencias = Asistencia::query()
            ->whereHas('reserva', fn ($q) => $q->where('charla_id', $charla->id))
            ->with(['reserva.usuario:id,nombre,email', 'validador:id,nombre'])
            ->latest('validada_en')
            ->paginate($perPage);

        $reservasActivas = $charla->reservas()->activas()->count();
        $asistentes = Asistencia::whereHas('reserva', fn ($q) => $q->where('charla_id', $charla->id)->activas())->count();

        return response()->json([
            'charla' => $charla->only(['id', 'titulo', 'aforo']),
            'resumen' => [
                'reservas_activas' => $reservasActivas,
                'asistentes' => $asistentes,
                'no_asistentes' => max($reservasActivas - $asistentes, 0),
            ],
            'asistencias' => $asistencias,
        ]);
    }
}
