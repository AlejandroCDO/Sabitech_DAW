<?php

namespace App\Api\Controllers\Admin;

use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Charla;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OAT;

/**
 * Reservas de una charla, vistas por el administrador.
 */
class ReservaCharlaController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/charlas/{charla}/reservas',
        operationId: 'adminListarReservasCharla',
        description: 'Reservas de una charla (de todas, incluidas canceladas; para '
            .'filtrar usa `estado`), de la más reciente a la más antigua, junto con un '
            .'resumen de la charla, con el usuario de cada reserva.',
        tags: ['Administración'],
        parameters: [
            new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12),
            new OAT\QueryParameter(name: 'estado', description: 'Filtrar por estado de la reserva.', required: false, schema: new OAT\Schema(type: 'string', enum: ['pendiente', 'confirmada', 'cancelada'])),
            new OAT\QueryParameter(name: 'per_page', description: 'Elementos por página (entre 1 y 100, por defecto 20).', required: false, example: 20),
            new OAT\QueryParameter(name: 'page', description: 'Página a consultar.', required: false, example: 1),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Reservas de la charla.', content: [
                new OAT\JsonContent(
                    type: 'object',
                    required: ['charla', 'reservas'],
                    properties: [
                        new OAT\Property(property: 'charla', description: 'Resumen de la charla.', type: 'object', properties: [
                            new OAT\Property(property: 'id', type: 'integer', example: 12),
                            new OAT\Property(property: 'titulo', type: 'string'),
                            new OAT\Property(property: 'aforo', type: 'integer', example: 50),
                            new OAT\Property(property: 'plazas_ocupadas', type: 'integer', example: 23),
                        ]),
                        new OAT\Property(property: 'reservas', type: 'object', description: 'Colección paginada de reservas.', properties: [
                            new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: '#/components/schemas/ReservaAdmin')),
                            new OAT\Property(property: 'meta', ref: '#/components/schemas/MetaPaginacion'),
                            new OAT\Property(property: 'links', ref: '#/components/schemas/UrlPaginacion'),
                        ]),
                    ],
                ),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function index(Request $request, Charla $charla): JsonResponse
    {
        $request->validate([
            'estado' => ['nullable', Rule::in(['pendiente', 'confirmada', 'cancelada'])],
        ]);

        $perPage = max(1, min($request->integer('per_page', 20), 100));

        $reservas = $charla->reservas()
            ->with('usuario:id,nombre,email,telefono')
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'charla' => $charla->only(['id', 'titulo', 'aforo', 'plazas_ocupadas']),
            'reservas' => $reservas,
        ]);
    }

    #[OAT\Post(
        path: '/api/admin/charlas/{charla}/reservas/{reserva}/cancelar',
        operationId: 'adminCancelarReserva',
        description: 'Cancela una reserva concreta y libera su plaza. Idempotente: '
            .'si ya estaba cancelada responde 200 sin cambios.',
        tags: ['Administración'],
        parameters: [
            new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12),
            new OAT\Parameter(name: 'reserva', description: 'Id de la reserva.', in: 'path', required: true, example: 41),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Reserva cancelada.', content: [
                new OAT\JsonContent(ref: '#/components/schemas/Reserva'),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    /**
     * Cancela una reserva concreta y libera su plaza.
     */
    public function cancelar(Charla $charla, Reserva $reserva): JsonResponse
    {
        DB::transaction(function () use ($charla, $reserva) {
            $charlaBloqueada = Charla::lockForUpdate()->findOrFail($charla->id);
            $reservaActual = Reserva::lockForUpdate()->findOrFail($reserva->id);

            if ($reservaActual->estado === EstadoReserva::Cancelada) {
                return;
            }

            $reservaActual->update(['estado' => EstadoReserva::Cancelada]);

            if ($charlaBloqueada->plazas_ocupadas > 0) {
                $charlaBloqueada->decrement('plazas_ocupadas');
            }
        });

        return response()->json($reserva->fresh());
    }
}
