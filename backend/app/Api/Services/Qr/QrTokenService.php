<?php

namespace App\Api\Services\Qr;

use App\Api\Exceptions\QrCaducadoException;
use App\Enums\EstadoReserva;
use App\Models\Reserva;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Genera y verifica los tokens QR de las reservas.
 *
 * Cada reserva tiene un secreto propio (reservas.qr_secret) que NUNCA sale del servidor.
 * El token que se pinta en el QR es "{reserva_id}.{firma}", donde la firma es un HMAC-SHA256
 * calculado con ese secreto. Sin el secreto no se puede falsificar un token, y si se rota
 * el secreto, el QR anterior deja de valer.
 *
 * El token cabe de sobra en asistencias.qr_token (varchar 128): id + "." + 43 caracteres.
 */
class QrTokenService
{
    private const CONTEXTO = 'sabitech-qr:v1:';

    /** Secreto aleatorio de 64 caracteres hexadecimales (cabe en reservas.qr_secret varchar(64)). */
    public function generarSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function generarToken(Reserva $reserva): string
    {
        return $reserva->id . '.' . $this->firmar($reserva->id, $reserva->qr_secret);
    }

    /**
     * Invalida el QR anterior generando un secreto nuevo (útil al reenviar la confirmación
     * si se sospecha que el QR se ha filtrado).
     */
    public function rotarSecret(Reserva $reserva): Reserva
    {
        $reserva->update(['qr_secret' => $this->generarSecret()]);

        return $reserva;
    }

    /**
     * Comprueba un token escaneado y devuelve su reserva (con charla y usuario cargados).
     *
     * @throws UnprocessableEntityHttpException si el token no es válido o la reserva/charla está cancelada
     * @throws QrCaducadoException              si la charla ya ha terminado
     */
    public function verificar(string $token): Reserva
    {
        $partes = explode('.', trim($token), 2);

        if (count($partes) !== 2 || !ctype_digit($partes[0]) || $partes[1] === '') {
            throw $this->qrInvalido();
        }

        $reserva = Reserva::with(['charla', 'usuario'])->find((int) $partes[0]);

        // Mismo error si la reserva no existe o la firma no cuadra: no se revela cuál de las dos falló.
        if (!$reserva || !hash_equals($this->firmar($reserva->id, $reserva->qr_secret), $partes[1])) {
            throw $this->qrInvalido();
        }

        if ($reserva->estado === EstadoReserva::Cancelada) {
            throw new UnprocessableEntityHttpException('La reserva está cancelada.');
        }

        if ($reserva->charla->cancelada) {
            throw new UnprocessableEntityHttpException('La charla ha sido cancelada.');
        }

        if (now()->greaterThan($reserva->charla->fin)) {
            throw new QrCaducadoException();
        }

        return $reserva;
    }

    private function firmar(int $reservaId, string $secret): string
    {
        $firma = hash_hmac('sha256', self::CONTEXTO . $reservaId, $secret, true);

        return rtrim(strtr(base64_encode($firma), '+/', '-_'), '=');
    }

    private function qrInvalido(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException('El código QR no es válido.');
    }
}