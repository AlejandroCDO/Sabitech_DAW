<?php

namespace App\Api\Resources\Admin;

use App\Api\Resources\UsuarioResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsistenciaAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reserva_id' => $this->reserva_id,
            'validado_por' => $this->validado_por,
            // Sin qr_token la validación fue manual; el token en sí no se expone.
            'metodo' => $this->qr_token === null ? 'manual' : 'qr',
            'validada_en' => $this->validada_en,
            'validador' => new UsuarioResource($this->whenLoaded('validador')),
            'reserva' => new ReservaAdminResource($this->whenLoaded('reserva')),
        ];
    }
}
