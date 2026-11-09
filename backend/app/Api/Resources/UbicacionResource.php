<?php

namespace App\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UbicacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'provincia' => $this->provincia,
            'municipio' => $this->municipio,
            'calle' => $this->calle,
            'numero' => $this->numero,
            'puerta' => $this->puerta,
            'codigo_postal' => $this->codigo_postal,
        ];
    }
}
