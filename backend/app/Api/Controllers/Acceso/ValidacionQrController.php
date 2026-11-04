<?php

namespace App\Api\Controllers\Acceso;

use App\Api\DTOs\Acceso\ValidacionQrData;
use App\Api\Resources\Admin\AsistenciaAdminResource;
use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Reserva;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Registra la asistencia a partir del QR que muestra el asistente.
 *
 * Token: {reservaId}.{ventana}.{firma}
 *   ventana = floor(time() / 30), con el reloj del servidor.
 *   firma   = hash_hmac('sha256', "{reservaId}.{ventana}", reservas.qr_secret).
 * Se aceptan la ventana actual y la anterior, así que un código vale entre 30 y 60 s.
 */
class ValidacionQrController extends Controller
{
    private const SEGUNDOS_VENTANA = 30;
    private const VENTANAS_TOLERADAS = 1;
    private const MINUTOS_ANTES = 30;

    public function __invoke(Request $request): JsonResponse
    {
        $datos = ValidacionQrData::fromArray($request->validate([
            'qr_token' => ['required', 'string', 'max:128'],
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]));

        if (! preg_match('/^(0|[1-9]\d{0,17})\.(0|[1-9]\d{0,11})\.([a-f0-9]{64})$/D', $datos->qrToken, $partes)) {
            return $this->qrInvalido();
        }

        $reservaId = (int) $partes[1];
        $ventana = (int) $partes[2];
        $firma = $partes[3];

        $reserva = Reserva::find($reservaId);
        if ($reserva === null) {
            return $this->qrInvalido();
        }

        $firmaEsperada = hash_hmac('sha256', "{$reservaId}.{$ventana}", $reserva->qr_secret);
        $ventanaActual = intdiv(time(), self::SEGUNDOS_VENTANA);

        if (! hash_equals($firmaEsperada, $firma)
            || $ventana > $ventanaActual
            || (int) $reserva->charla_id !== $datos->charlaId) {
            return $this->qrInvalido();
        }

        try {
            return DB::transaction(function () use ($reservaId, $ventana, $ventanaActual, $datos, $request) {
                $reserva = Reserva::with('charla')->lockForUpdate()->findOrFail($reservaId);

                // Antes que la caducidad: repetir un código ya usado debe decir "ya registrada".
                if ($reserva->asistencia()->exists()) {
                    return $this->error('La asistencia de esta reserva ya está registrada.', 'asistencia_ya_registrada', 409);
                }

                if ($ventana < $ventanaActual - self::VENTANAS_TOLERADAS) {
                    return $this->error('El código QR ha caducado. Pide al asistente que muestre el código actual.', 'qr_caducado', 422);
                }

                if ($reserva->estado !== EstadoReserva::Confirmada) {
                    return $this->error('La reserva no está confirmada.', 'reserva_no_activa', 409);
                }

                $charla = $reserva->charla;
                if ($charla->cancelada) {
                    return $this->error('La charla está cancelada; no se puede registrar asistencia.', 'charla_cancelada', 409);
                }

                $ahora = now();
                if ($ahora->lt($charla->inicio->copy()->subMinutes(self::MINUTOS_ANTES)) || $ahora->gt($charla->fin)) {
                    return $this->error('Fuera de plazo: se registra desde '.self::MINUTOS_ANTES.' minutos antes del inicio hasta el fin de la charla.', 'fuera_de_plazo', 409);
                }

                $asistencia = Asistencia::create([
                    'reserva_id' => $reserva->id,
                    'validado_por' => $request->user()->id,
                    'qr_token' => $datos->qrToken,
                    'validada_en' => $ahora,
                ]);

                return (new AsistenciaAdminResource($asistencia))->response()->setStatusCode(201);
            });
        } catch (UniqueConstraintViolationException) {
            return $this->error('La asistencia de esta reserva ya está registrada.', 'asistencia_ya_registrada', 409);
        }
    }

    /** Mismo mensaje para todo fallo del código: no revela si la reserva existe. */
    private function qrInvalido(): JsonResponse
    {
        return $this->error('El código QR no es válido para esta charla.', 'qr_invalido', 422);
    }

    private function error(string $mensaje, string $codigo, int $estado): JsonResponse
    {
        return response()->json(['message' => $mensaje, 'code' => $codigo], $estado);
    }
}
