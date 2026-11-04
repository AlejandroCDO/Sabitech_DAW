<?php

namespace App\Api\Resources\Admin;

use App\Api\Resources\UsuarioResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservaAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'charla_id' => $this->charla_id,
            'estado' => $this->estado,
            'email_intentos' => $this->email_intentos,
            'email_ultimo_envio' => $this->email_ultimo_envio,
            'created_at' => $this->created_at,
            'usuario' => new UsuarioResource($this->whenLoaded('usuario')),
            'asistencia' => new AsistenciaAdminResource($this->whenLoaded('asistencia')),
        ];
    }
}
