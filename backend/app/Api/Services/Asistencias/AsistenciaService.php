<?php

namespace App\Api\Services\Asistencias;

use App\Api\DTOs\Acceso\ValidacionManualData;
use App\Api\DTOs\Acceso\ValidacionQrData;
use App\Enums\EstadoReserva;
use App\Models\Asistencia;
use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registra la asistencia por QR o de forma manual.
 *
 * Token: {reservaId}.{ventana}.{firma}
 *   ventana = floor(time() / 30), con el reloj del servidor.
 *   firma   = hash_hmac('sha256', "{reservaId}.{ventana}", reservas.qr_secret).
 * Se aceptan la ventana actual y la anterior, así que un código vale entre 30 y 60 s.
 */
class AsistenciaService
{
    private const SEGUNDOS_VENTANA = 30;
    private const VENTANAS_TOLERADAS = 1;
    private const MINUTOS_ANTES = 30;

    public function registrarPorQr(ValidacionQrData $datos, Usuario $validador): Asistencia
    {
        if (! preg_match('/^(0|[1-9]\d{0,17})\.(0|[1-9]\d{0,11})\.([a-f0-9]{64})$/D', $datos->qrToken, $partes)) {
            $this->qrInvalido();
        }

        $reservaId = (int) $partes[1];
        $ventana = (int) $partes[2];
        $firma = $partes[3];

        $reserva = Reserva::find($reservaId);
        if ($reserva === null) {
            $this->qrInvalido();
        }

        $firmaEsperada = hash_hmac('sha256', "{$reservaId}.{$ventana}", $reserva->qr_secret);
        $ventanaActual = intdiv(time(), self::SEGUNDOS_VENTANA);

        if (! hash_equals($firmaEsperada, $firma)
            || $ventana > $ventanaActual
            || (int) $reserva->charla_id !== $datos->charlaId) {
            $this->qrInvalido();
        }

        return $this->registrar($reservaId, $datos->qrToken, $ventana, $ventanaActual, $validador);
    }

    public function registrarManual(ValidacionManualData $datos, Usuario $validador): Asistencia
    {
        if ((int) Reserva::whereKey($datos->reservaId)->value('charla_id') !== $datos->charlaId) {
            throw ValidationException::withMessages([
                'reserva_id' => 'La reserva no pertenece a la charla indicada.',
            ]);
        }

        return $this->registrar($datos->reservaId, null, null, null, $validador);
    }

    /** $ventana y $ventanaActual solo se informan en el flujo QR (comprueban la caducidad). */
    private function registrar(int $reservaId, ?string $qrToken, ?int $ventana, ?int $ventanaActual, Usuario $validador): Asistencia
    {
        try {
            return DB::transaction(function () use ($reservaId, $qrToken, $ventana, $ventanaActual, $validador) {
                $reserva = Reserva::with('charla')->lockForUpdate()->findOrFail($reservaId);

                // Antes que la caducidad: repetir un código ya usado debe decir "ya registrada".
                if ($reserva->asistencia()->exists()) {
                    $this->error('La asistencia de esta reserva ya está registrada.', 'asistencia_ya_registrada', 409);
                }

                if ($qrToken !== null && $ventana < $ventanaActual - self::VENTANAS_TOLERADAS) {
                    $this->error('El código QR ha caducado. Pide al asistente que muestre el código actual.', 'qr_caducado', 422);
                }

                if ($reserva->estado !== EstadoReserva::Confirmada) {
                    $this->error('La reserva no está confirmada.', 'reserva_no_activa', 409);
                }

                $charla = $reserva->charla;
                if ($charla->cancelada) {
                    $this->error('La charla está cancelada; no se puede registrar asistencia.', 'charla_cancelada', 409);
                }

                $ahora = now();
                if ($ahora->lt($charla->inicio->copy()->subMinutes(self::MINUTOS_ANTES)) || $ahora->gt($charla->fin)) {
                    $this->error('Fuera de plazo: se registra desde '.self::MINUTOS_ANTES.' minutos antes del inicio hasta el fin de la charla.', 'fuera_de_plazo', 409);
                }

                return Asistencia::create([
                    'reserva_id' => $reserva->id,
                    'validado_por' => $validador->id,
                    'qr_token' => $qrToken,
                    'validada_en' => $ahora,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            $this->error('La asistencia de esta reserva ya está registrada.', 'asistencia_ya_registrada', 409);
        }
    }

    /** Mismo mensaje para todo fallo del código: no revela si la reserva existe. */
    private function qrInvalido(): never
    {
        $this->error('El código QR no es válido para esta charla.', 'qr_invalido', 422);
    }

    private function error(string $mensaje, string $codigo, int $estado): never
    {
        throw new HttpResponseException(response()->json(['message' => $mensaje, 'code' => $codigo], $estado));
    }
}
