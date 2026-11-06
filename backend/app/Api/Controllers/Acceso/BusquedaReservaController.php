<?php

namespace App\Api\Controllers\Acceso;

use App\Api\Resources\Admin\ReservaAdminResource;
use App\Http\Controllers\Controller;
use App\Models\Charla;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OAT;

/** Busca las reservas activas de una charla por nombre o email del asistente. */
final class BusquedaReservaController extends Controller
{
    #[OAT\Get(
        path: '/api/acceso/charlas/{charla}/reservas',
        operationId: 'buscarReservasAcceso',
        description: 'Reservas activas (no canceladas) de una charla, ordenadas por id, '
            .'para localizar al asistente en la puerta. Si se envía `q`, filtra por '
            .'coincidencia en nombre o email del asistente. Incluye los datos del '
            .'usuario y, si existe, la asistencia asociada, para poder validar manualmente.',
        tags: ['Personal de acceso'],
        parameters: [
            new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12),
            new OAT\QueryParameter(name: 'q', description: 'Texto a buscar en nombre o email del asistente (máx. 100).', required: false, example: 'Alberto'),
            new OAT\QueryParameter(name: 'per_page', description: 'Elementos por página (entre 1 y 100, por defecto 20).', required: false, example: 20),
            new OAT\QueryParameter(name: 'page', description: 'Página a consultar.', required: false, example: 1),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Listado paginado de reservas activas de la charla.', content: [
                new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: '#/components/schemas/ReservaAdmin')),
                        new OAT\Property(property: 'meta', ref: '#/components/schemas/MetaPaginacion'),
                        new OAT\Property(property: 'links', ref: '#/components/schemas/UrlPaginacion'),
                    ],
                ),
            ]),
            new OAT\Response(response: '401', ref: '#/components/responses/401'),
            new OAT\Response(response: '403', ref: '#/components/responses/403'),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function __invoke(Request $request, Charla $charla): AnonymousResourceCollection
    {
        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $perPage = max(1, min($request->integer('per_page', 20), 100));
        $q = $datos['q'] ?? null;

        $reservas = $charla->reservas()
            ->activas()
            ->with(['usuario', 'asistencia'])
            ->when($q !== null && $q !== '', function ($query) use ($q) {
                // ESCAPE explícito: funciona igual en MySQL y en SQLite.
                $patron = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q).'%';
                $query->whereHas('usuario', fn ($u) => $u
                    ->whereRaw("nombre LIKE ? ESCAPE '!'", [$patron])
                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$patron]));
            })
            ->orderBy('id')
            ->paginate($perPage);

        return ReservaAdminResource::collection($reservas);
    }
}
