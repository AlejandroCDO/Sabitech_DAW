<?php

namespace App\Api\DTOs\Reservas;

final readonly class CrearReservaData
{
    public function __construct(
        public int $usuarioId,
        public int $charlaId,
    ) {}

    /**
     * Recibe los datos ya validados. El usuario_id lo asigna el controlador
     * con el usuario autenticado; nunca se acepta del cliente.
     */
    public static function fromArray(array $datos): self
    {
        return new self(
            usuarioId: (int) $datos['usuario_id'],
            charlaId: (int) $datos['charla_id'],
        );
    }

    /** Claves con los nombres de las columnas de la tabla reservas. */
    public function toArray(): array
    {
        return [
            'usuario_id' => $this->usuarioId,
            'charla_id' => $this->charlaId,
        ];
    }
}
