<?php

namespace App\Api\Controllers\Asistente;

use App\Api\DTOs\Reservas\CrearReservaData;
use App\Enums\EstadoReserva;
use App\Http\Controllers\Controller;
use App\Models\Charla;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReservaController extends Controller
{
    /**
     * Reservas del usuario autenticado, de la más reciente a la más antigua.
     */
    public function index(Request $request): JsonResponse
    {
        $reservas = $request->user()
            ->reservas()
            ->with(['charla.ubicacion', 'charla.ponente'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reservas);
    }

    /**
     * Reserva la charla indicada para el usuario autenticado.
     *
     * La exclusión mutua sobre la fila de la charla garantiza que la
     * concurrencia de última plaza no supere el aforo.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ]);

        $reservaData = CrearReservaData::fromArray($datos + [
            'usuario_id' => $request->user()->id,
        ]);

        $reserva = DB::transaction(function () use ($reservaData) {
            $charla = Charla::lockForUpdate()->findOrFail($reservaData->charlaId);

            if ($charla->cancelada) {
                abort(409, 'La charla está cancelada y no admite reservas.');
            }

            if (now()->gte($charla->inicio)) {
                abort(409, 'La charla ya ha empezado y no admite reservas.');
            }

            $existente = Reserva::where('usuario_id', $reservaData->usuarioId)
                ->where('charla_id', $reservaData->charlaId)
                ->first();

            if ($existente !== null && $existente->estado !== EstadoReserva::Cancelada) {
                abort(409, 'Ya tienes una reserva para esta charla.');
            }

            if ($charla->plazas_ocupadas >= $charla->aforo) {
                abort(409, 'No quedan plazas disponibles para esta charla.');
            }

            if ($existente !== null) {
                // Se reactiva la reserva cancelada en lugar de crear una fila
                // nueva (el índice único incluye clave_activa) y la plaza
                // vuelve a ocuparse.
                $existente->update([
                    'estado' => EstadoReserva::Confirmada,
                    'qr_secret' => self::generarQrSecret(),
                    'email_intentos' => 0,
                    'email_ultimo_envio' => null,
                ]);

                $charla->increment('plazas_ocupadas');

                return $existente;
            }

            $reserva = Reserva::create([
                'usuario_id' => $reservaData->usuarioId,
                'charla_id' => $reservaData->charlaId,
                'estado' => EstadoReserva::Confirmada,
                'qr_secret' => self::generarQrSecret(),
            ]);

            $charla->increment('plazas_ocupadas');

            return $reserva;
        });

        // La plaza queda garantizada al reservar (estado confirmada). Si el
        // correo falla la reserva no se pierde; queda pendiente de
        // reenvío y el usuario puede solicitarlo desde su listado.
        $this->enviarConfirmacion($reserva);

        return response()->json(
            $reserva->load(['charla.ubicacion', 'charla.ponente']),
            201,
        );
    }

    /**
     * Cancela una reserva propia y libera la plaza.
     *
     * Si ya estaba cancelada no se toca nada (operación idempotente).
     */
    public function cancelar(Request $request, Reserva $reserva): JsonResponse
    {
        if ($reserva->usuario_id !== $request->user()->id) {
            abort(404); // no revela reservas de otros usuarios
        }

        DB::transaction(function () use ($reserva) {
            $actual = Reserva::lockForUpdate()->findOrFail($reserva->id);

            if ($actual->estado === EstadoReserva::Cancelada) {
                return;
            }

            $actual->update(['estado' => EstadoReserva::Cancelada]);

            $charla = Charla::lockForUpdate()->findOrFail($actual->charla_id);

            if (! $charla->cancelada) {
                $charla->decrement('plazas_ocupadas');
            }
        });

        return response()->json(
            $reserva->fresh(['charla.ubicacion', 'charla.ponente']),
        );
    }

    /**
     * Envía el correo de confirmación con la vista disponible
     * (resources/views/email/confirmacion-reserva.blade.php), que recibirá
     * la reserva (con charla, ubicación, ponente y usuario) y la referencia.
     *
     * Solo se registran envíos reales; un fallo del correo no consume intentos.
     */
    private function enviarConfirmacion(Reserva $reserva): bool
    {
        $reserva->load(['charla.ubicacion', 'charla.ponente', 'usuario']);

        try {
            Mail::send('email.confirmacion-reserva', [
                'reserva' => $reserva,
                'referencia' => self::referenciaDe($reserva),
            ], function ($mensaje) use ($reserva) {
                $mensaje->to($reserva->usuario->email, $reserva->usuario->nombre)
                    ->subject('Confirmación de reserva: '.$reserva->charla->titulo);
            });
        } catch (Throwable) {
            return false;
        }

        $reserva->update([
            'email_intentos' => $reserva->email_intentos + 1,
            'email_ultimo_envio' => now(),
        ]);

        return true;
    }

    /** Referencia estable asociada a la reserva (SAB-000031). */
    private static function referenciaDe(Reserva $reserva): string
    {
        return 'SAB-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT);
    }

    /** Secreto aleatorio de 64 caracteres, único en reservas (uq_reservas_qr_secret). */
    private static function generarQrSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
}
