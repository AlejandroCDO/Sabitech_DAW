<?php

namespace App\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharlaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'inicio' => $this->inicio->toIso8601String(),
            'fin' => $this->fin->toIso8601String(),
            'aforo' => $this->aforo,
            'plazas_disponibles' => max(0, $this->aforo - $this->plazas_ocupadas),
            'cancelada' => (bool) $this->cancelada,
            'ponente' => PonenteResource::make($this->whenLoaded('ponente')),
            'ubicacion' => UbicacionResource::make($this->whenLoaded('ubicacion')),
        ];
    }
}
