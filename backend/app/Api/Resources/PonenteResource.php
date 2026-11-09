<?php

namespace App\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PonenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'biografia' => $this->biografia,
            'foto_url' => $this->foto_url,
            'especialidad' => $this->especialidad,
            'linkedin_url' => $this->linkedin_url,
        ];
    }
}
