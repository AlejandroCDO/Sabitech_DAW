<?php

namespace App\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Asistencia registrada, vista de la reserva del asistente.
 * Sin qr_token la validación fue manual; el token en sí no se expone.
 */
class AsistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reserva_id,
            'metodo' => $this->qr_token === null ? 'manual' : 'qr',
            'validada_en' => $this->validada_en,
            'reserva' => new ReservaResource($this->whenLoaded('reserva')),
        ];
    }
}
