<?php

namespace App\Api\Services\Reservas;

use App\Api\DTOs\Reservas\CrearReservaData;
use App\Api\Exceptions\ReservaDuplicadaException;
use App\Api\Exceptions\ReservaNoActivaException;
use App\Api\Exceptions\SinPlazasException;
use App\Api\Services\Qr\QrTokenService;
use App\Enums\EstadoReserva;
use App\Models\Charla;
use App\Models\Reserva;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Lógica de negocio de las reservas del asistente.
 *
 * El bloqueo de fila sobre la charla garantiza que, aunque dos personas
 * soliciten la última plaza al mismo tiempo, nunca se supere el aforo.
 * La propiedad de la reserva (usuario_id) la comprueba el controlador.
 */
class ReservaService
{
    public function __construct(
        private readonly QrTokenService $qrToken,
        private readonly ConfirmacionReservaService $confirmaciones,
    ) {}

    /**
     * Reserva la charla indicada para el usuario autenticado.
     *
     * La plaza queda garantizada al reservar: el estado inicial es confirmada.
     * Si el usuario tenía una reserva cancelada para la misma charla, la
     * reactiva en lugar de crear una fila nueva: el índice único
     * (usuario_id, charla_id, clave_activa) solo admite una reserva activa
     * por usuario y charla.
     */
    public function crear(CrearReservaData $datos): Reserva
    {
        try {
            $reserva = DB::transaction(function () use ($datos) {
                $charla = Charla::lockForUpdate()->findOrFail($datos->charlaId);

                if ($charla->cancelada) {
                    throw new ConflictHttpException('La charla está cancelada y no admite reservas.');
                }

                if (now()->gte($charla->inicio)) {
                    throw new ConflictHttpException('La charla ya ha empezado y no admite reservas.');
                }

                if ($charla->plazas_ocupadas >= $charla->aforo) {
                    throw new SinPlazasException;
                }

                $existente = Reserva::where('usuario_id', $datos->usuarioId)
                    ->where('charla_id', $datos->charlaId)
                    ->first();

                if ($existente !== null) {
                    if ($existente->estado !== EstadoReserva::Cancelada) {
                        throw new ReservaDuplicadaException;
                    }

                    $existente->update([
                        'estado' => EstadoReserva::Confirmada,
                        'qr_secret' => $this->qrToken->generarSecret(),
                        'email_intentos' => 0,
                        'email_ultimo_envio' => null,
                    ]);

                    $charla->increment('plazas_ocupadas');

                    return $existente;
                }

                $reserva = Reserva::create([
                    'usuario_id' => $datos->usuarioId,
                    'charla_id' => $datos->charlaId,
                    'estado' => EstadoReserva::Confirmada,
                    'qr_secret' => $this->qrToken->generarSecret(),
                ]);

                $charla->increment('plazas_ocupadas');

                return $reserva;
            });
        } catch (UniqueConstraintViolationException) {
            // Dos usuarios reservan a la vez y justo queda una plaza: el
            // índice único de reservas deshace el segundo intento.
            throw new ReservaDuplicadaException;
        }

        // Fuera de la transacción: si el correo falla, la reserva se
        // conserva y queda pendiente de reenvío.
        $this->confirmaciones->enviar($reserva);

        return $reserva->load(['charla.ubicacion', 'charla.ponente']);
    }

    /**
     * Cancela una reserva y libera su plaza.
     *
     * Lanza ReservaNoActivaException si ya estaba cancelada, para que la
     * petición repetida sea visible en lugar de responder como acertada.
     */
    public function cancelar(Reserva $reserva): Reserva
    {
        DB::transaction(function () use ($reserva) {
            $actual = Reserva::lockForUpdate()->findOrFail($reserva->id);

            if ($actual->estado === EstadoReserva::Cancelada) {
                throw new ReservaNoActivaException('Esta reserva ya está cancelada.');
            }

            $actual->update(['estado' => EstadoReserva::Cancelada]);

            $charla = Charla::lockForUpdate()->findOrFail($actual->charla_id);

            if (! $charla->cancelada) {
                $charla->decrement('plazas_ocupadas');
            }
        });

        return $reserva->fresh(['charla.ubicacion', 'charla.ponente']);
    }
}
