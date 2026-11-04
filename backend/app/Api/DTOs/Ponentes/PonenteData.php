<?php

namespace App\Api\DTOs\Ponentes;

final readonly class PonenteData
{
    public function __construct(
        public string $nombre,
        public ?string $biografia = null,
        public ?string $fotoUrl = null,
        public ?string $especialidad = null,
        public ?string $linkedinUrl = null,
    ) {
    }

    /** Recibe los datos ya validados (FormRequest->validated()). */
    public static function fromArray(array $datos): self
    {
        return new self(
            nombre: trim($datos['nombre']),
            biografia: self::textoONull($datos['biografia'] ?? null),
            fotoUrl: self::textoONull($datos['foto_url'] ?? null),
            especialidad: self::textoONull($datos['especialidad'] ?? null),
            linkedinUrl: self::textoONull($datos['linkedin_url'] ?? null),
        );
    }

    /** Claves con los nombres de las columnas de la tabla ponentes. */
    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'biografia' => $this->biografia,
            'foto_url' => $this->fotoUrl,
            'especialidad' => $this->especialidad,
            'linkedin_url' => $this->linkedinUrl,
        ];
    }

    private static function textoONull(?string $valor): ?string
    {
        $valor = $valor === null ? null : trim($valor);

        return $valor === '' ? null : $valor;
    }
}