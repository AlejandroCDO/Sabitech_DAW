<?php

namespace App\Api\DTOs\Charlas;

use Carbon\CarbonImmutable;

final readonly class FiltroCharlasData
{
    public const POR_PAGINA_MAXIMO = 50;

    public function __construct(
        public ?string $provincia = null,
        public ?string $municipio = null,
        public ?int $ponenteId = null,
        public ?CarbonImmutable $desde = null,
        public ?CarbonImmutable $hasta = null,
        public bool $soloConPlazas = false,
        public ?string $busqueda = null,
        public int $pagina = 1,
        public int $porPagina = 10,
    ) {
    }

    /** Recibe los parámetros de la URL ya validados. */
    public static function fromArray(array $datos): self
    {
        return new self(
            provincia: self::textoONull($datos['provincia'] ?? null),
            municipio: self::textoONull($datos['municipio'] ?? null),
            ponenteId: self::textoONull($datos['ponente_id'] ?? null) !== null
                ? (int) $datos['ponente_id']
                : null,
            desde: self::fechaONull($datos['desde'] ?? null, false),
            hasta: self::fechaONull($datos['hasta'] ?? null, true),
            soloConPlazas: filter_var($datos['solo_con_plazas'] ?? false, FILTER_VALIDATE_BOOLEAN),
            busqueda: self::textoONull($datos['q'] ?? null),
            pagina: max(1, (int) ($datos['pagina'] ?? 1)),
            porPagina: min(self::POR_PAGINA_MAXIMO, max(1, (int) ($datos['por_pagina'] ?? 10))),
        );
    }

    private static function textoONull(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }

    private static function fechaONull(mixed $valor, bool $finDeDia): ?CarbonImmutable
    {
        $texto = self::textoONull($valor);

        if ($texto === null) {
            return null;
        }

        $fecha = CarbonImmutable::parse($texto);

        return $finDeDia ? $fecha->endOfDay() : $fecha->startOfDay();
    }
}