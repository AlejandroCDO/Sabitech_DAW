<?php

namespace App\Api\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\JsonResponse;

/** Provincias con alguna ubicación, para filtrar el catálogo de charlas. */
class ProvinciaController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => Ubicacion::query()->distinct()->orderBy('provincia')->pluck('provincia'),
        ]);
    }
}
