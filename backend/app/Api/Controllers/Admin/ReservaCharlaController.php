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

/**
 * Reservas de una charla, vistas por el administrador.
 */
class ReservaCharlaController extends Controller
{
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

    /**
     * Cancela una reserva concreta y libera su plaza.
     */
    public function cancelar(Charla $charla, Reserva $reserva): JsonResponse
    {
        DB::transaction(function () use ($charla, $reserva) {
            $charlaBloqueada = Charla::lockForUpdate()->findOrFail($charla->id);
            $reservaActual   = Reserva::lockForUpdate()->findOrFail($reserva->id);

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