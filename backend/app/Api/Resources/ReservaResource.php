<?php

namespace App\Api\Resources;

use App\Api\Services\Reservas\ReferenciaGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Reserva desde la vista del asistente.
 * El secreto del QR queda oculto por el propio modelo (#[Hidden]).
 */
class ReservaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'referencia' => ReferenciaGenerator::generar($this->resource),
            'estado' => $this->estado,
            'email_intentos' => $this->email_intentos,
            'email_ultimo_envio' => $this->email_ultimo_envio,
            'created_at' => $this->created_at,
            'charla' => new CharlaResource($this->whenLoaded('charla')),
        ];
    }
}
