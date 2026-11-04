<?php

namespace App\Api\Controllers\Acceso;

use App\Api\DTOs\Acceso\ValidacionManualData;
use App\Api\Resources\Admin\AsistenciaAdminResource;
use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Reserva;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Registra la asistencia sin QR, cuando el asistente no puede mostrar su código. */
class ValidacionManualController extends Controller
{
    private const MINUTOS_ANTES = 30;

    public function __invoke(Request $request): JsonResponse
    {
        $datos = ValidacionManualData::fromArray($request->validate([
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]));

        if ((int) Reserva::whereKey($datos->reservaId)->value('charla_id') !== $datos->charlaId) {
            throw ValidationException::withMessages([
                'reserva_id' => 'La reserva no pertenece a la charla indicada.',
            ]);
        }

        try {
            return DB::transaction(function () use ($datos, $request) {
                $reserva = Reserva::with('charla')->lockForUpdate()->findOrFail($datos->reservaId);

                if ($reserva->asistencia()->exists()) {
                    return $this->error('La asistencia de esta reserva ya está registrada.', 'asistencia_ya_registrada');
                }

                if ($reserva->estado !== EstadoReserva::Confirmada) {
                    return $this->error('La reserva no está confirmada.', 'reserva_no_activa');
                }

                $charla = $reserva->charla;
                if ($charla->cancelada) {
                    return $this->error('La charla está cancelada; no se puede registrar asistencia.', 'charla_cancelada');
                }

                $ahora = now();
                if ($ahora->lt($charla->inicio->copy()->subMinutes(self::MINUTOS_ANTES)) || $ahora->gt($charla->fin)) {
                    return $this->error('Fuera de plazo: se registra desde '.self::MINUTOS_ANTES.' minutos antes del inicio hasta el fin de la charla.', 'fuera_de_plazo');
                }

                $asistencia = Asistencia::create([
                    'reserva_id' => $reserva->id,
                    'validado_por' => $request->user()->id,
                    'qr_token' => null,
                    'validada_en' => $ahora,
                ]);

                return (new AsistenciaAdminResource($asistencia))->response()->setStatusCode(201);
            });
        } catch (UniqueConstraintViolationException) {
            return $this->error('La asistencia de esta reserva ya está registrada.', 'asistencia_ya_registrada');
        }
    }

    private function error(string $mensaje, string $codigo): JsonResponse
    {
        return response()->json(['message' => $mensaje, 'code' => $codigo], 409);
    }
}
