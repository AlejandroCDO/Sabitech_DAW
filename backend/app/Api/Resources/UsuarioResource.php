<?php

namespace App\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fecha_nacimiento?->toDateString(),
            'rol' => $this->whenLoaded('rol', fn () => $this->rol->nombre),
        ];
    }
}
