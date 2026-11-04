<?php

namespace App\Api\Controllers\Acceso;

use App\Api\Resources\Admin\ReservaAdminResource;
use App\Http\Controllers\Controller;
use App\Models\Charla;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Busca las reservas activas de una charla por nombre o email del asistente. */
class BusquedaReservaController extends Controller
{
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
