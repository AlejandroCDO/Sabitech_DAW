<?php

namespace App\Api\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Charla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;

/**
 * Catálogo público: no requiere sesión.
 */
final class CharlaController extends Controller
{
    #[OAT\Get(
        path: '/api/charlas',
        operationId: 'listarCharlasPublicas',
        description: 'Catálogo de charlas activas para el público: solo charlas no canceladas que aún no han '
            .'empezado (su `fin` coincide con la hora actual o es posterior), ordenadas por fecha de inicio. '
            .'Admite filtrar por provincia y paginar. Cada elemento trae ponente y ubicación.',
        tags: ['Público'],
        parameters: [
            new OAT\QueryParameter(name: 'provincia', description: 'Filtrar por provincia de la ubicación.', required: false, example: 'Alicante'),
            new OAT\QueryParameter(name: 'per_page', description: 'Elementos por página (entre 1 y 100, por defecto 15).', required: false, example: 15),
            new OAT\QueryParameter(name: 'page', description: 'Página a consultar.', required: false, example: 1),
        ],
        responses: [
            new OAT\Response(
                response: '200',
                description: 'Listado paginado de charlas activas.',
                content: [new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: '#/components/schemas/CharlaCatalogo')),
                        new OAT\Property(property: 'meta', ref: '#/components/schemas/MetaPaginacion'),
                        new OAT\Property(property: 'links', ref: '#/components/schemas/UrlPaginacion'),
                    ],
                    type: 'object',
                )],
            ),
            new OAT\Response(response: '422', ref: '#/components/responses/422'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'provincia' => ['nullable', 'string', 'max:100'],
        ]);

        $perPage = max(1, min($request->integer('per_page', 15), 100));
        $provincia = $datos['provincia'] ?? null;

        $charlas = Charla::query()
            ->with(['ponente', 'ubicacion'])
            ->where('cancelada', false)
            ->where('fin', '>=', now())
            ->when($provincia !== null && $provincia !== '', fn ($query) => $query
                ->whereHas('ubicacion', fn ($u) => $u->where('provincia', $provincia)))
            ->orderBy('inicio')
            ->paginate($perPage);

        return response()->json($charlas->through(fn (Charla $charla) => $this->formato($charla)));
    }

    #[OAT\Get(
        path: '/api/charlas/{charla}',
        operationId: 'mostrarCharlaPublica',
        description: 'Detalle de una charla. A diferencia del listado, también devuelve '
            .'charlas ya canceladas (campos `cancelada: true`).',
        tags: ['Público'],
        parameters: [
            new OAT\Parameter(name: 'charla', description: 'Id de la charla.', in: 'path', required: true, example: 12),
        ],
        responses: [
            new OAT\Response(response: '200', description: 'Detalle de la charla con su ponente y su ubicación.', content: [
                new OAT\JsonContent(properties: [new OAT\Property(property: 'data', ref: '#/components/schemas/CharlaCatalogo')], type: 'object'),
            ]),
            new OAT\Response(response: '404', ref: '#/components/responses/404'),
        ],
    )]
    public function show(Charla $charla): JsonResponse
    {
        $charla->load(['ponente', 'ubicacion']);

        return response()->json(['data' => $this->formato($charla)]);
    }

    private function formato(Charla $charla): array
    {
        return [
            'id' => $charla->id,
            'titulo' => $charla->titulo,
            'descripcion' => $charla->descripcion,
            'inicio' => $charla->inicio,
            'fin' => $charla->fin,
            'aforo' => $charla->aforo,
            'plazas_disponibles' => $charla->plazas_disponibles,
            'cancelada' => $charla->cancelada,
            'ponente' => $charla->ponente?->only(['id', 'nombre', 'biografia', 'foto_url', 'especialidad', 'linkedin_url']),
            'ubicacion' => $charla->ubicacion?->only(['id', 'nombre', 'provincia', 'municipio', 'calle', 'codigo_postal', 'numero', 'puerta']),
        ];
    }
}
