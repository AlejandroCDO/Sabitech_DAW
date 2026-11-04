<?php

namespace App\Api\DTOs\Charlas;

use Carbon\CarbonImmutable;

final readonly class CharlaData
{
    public function __construct(
        public int $ponenteId,
        public int $ubicacionId,
        public string $titulo,
        public string $descripcion,
        public CarbonImmutable $inicio,
        public CarbonImmutable $fin,
        public int $aforo,
    ) {
    }

    /** Recibe los datos ya validados (FormRequest->validated()). */
    public static function fromArray(array $datos): self
    {
        return new self(
            ponenteId: (int) $datos['ponente_id'],
            ubicacionId: (int) $datos['ubicacion_id'],
            titulo: trim($datos['titulo']),
            descripcion: trim($datos['descripcion']),
            inicio: CarbonImmutable::parse($datos['inicio']),
            fin: CarbonImmutable::parse($datos['fin']),
            aforo: (int) $datos['aforo'],
        );
    }

    /** Claves con los nombres de las columnas de la tabla charlas. */
    public function toArray(): array
    {
        return [
            'ponente_id' => $this->ponenteId,
            'ubicacion_id' => $this->ubicacionId,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'inicio' => $this->inicio,
            'fin' => $this->fin,
            'aforo' => $this->aforo,
        ];
    }
}