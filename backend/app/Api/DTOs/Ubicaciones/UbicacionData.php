<?php

namespace App\Api\DTOs\Ubicaciones;

final readonly class UbicacionData
{
    public function __construct(
        public string $provincia,
        public string $municipio,
        public string $calle,
        public string $codigoPostal,
        public string $numero,
        public ?string $puerta,
        public string $nombre,
    ) {}

    /** Recibe los datos ya validados (FormRequest->validated()). */
    public static function fromArray(array $datos): self
    {
        $puerta = trim((string) ($datos['puerta'] ?? ''));

        return new self(
            provincia: trim($datos['provincia']),
            municipio: trim($datos['municipio']),
            calle: trim($datos['calle']),
            codigoPostal: trim($datos['codigo_postal']),
            numero: trim($datos['numero']),
            puerta: $puerta !== '' ? $puerta : null,
            nombre: trim($datos['nombre']),
        );
    }

    /** Claves con los nombres de las columnas de la tabla ubicaciones. */
    public function toArray(): array
    {
        return [
            'provincia' => $this->provincia,
            'municipio' => $this->municipio,
            'calle' => $this->calle,
            'codigo_postal' => $this->codigoPostal,
            'numero' => $this->numero,
            'puerta' => $this->puerta,
            'nombre' => $this->nombre,
        ];
    }
}
