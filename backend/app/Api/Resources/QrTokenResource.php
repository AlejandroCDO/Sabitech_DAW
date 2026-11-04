<?php

namespace App\Api\Resources;

use App\Api\DTOs\Qr\QrTokenData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property QrTokenData $resource */
class QrTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'reserva_id' => $this->resource->reservaId,
            'token' => $this->resource->token,
            'expira_en' => $this->resource->expiraEn,
        ];
    }
}
