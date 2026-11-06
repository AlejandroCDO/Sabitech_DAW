<?php

namespace App\Api\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OAT;

/** Provincias con alguna ubicación, para filtrar el catálogo de charlas. */
final class ProvinciaController extends Controller
{
    #[OAT\Get(
        path: '/api/provincias',
        operationId: 'listarProvincias',
        description: 'Provincias (sin repetir y ordenadas alfabéticamente) que tienen alguna '
            .'ubicación registrada. Se usa para el filtro de provincia del catálogo.',
        tags: ['Público'],
        responses: [
            new OAT\Response(response: '200', description: 'Provincias con al menos una ubicación.', content: [
                new OAT\JsonContent(
                    type: 'object',
                    properties: [new OAT\Property(
                        property: 'data',
                        description: 'Lista de nombres de provincia.',
                        type: 'array',
                        items: new OAT\Items(type: 'string', example: 'Alicante'),
                    )],
                ),
            ]),
        ],
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => Ubicacion::query()->distinct()->orderBy('provincia')->pluck('provincia'),
        ]);
    }
}
