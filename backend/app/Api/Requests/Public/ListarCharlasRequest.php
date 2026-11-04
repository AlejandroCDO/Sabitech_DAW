<?php

namespace App\Api\Requests\Public;

use App\Api\DTOs\Charlas\FiltroCharlasData;
use Illuminate\Foundation\Http\FormRequest;

/** Filtros del catálogo público de charlas (coinciden con FiltroCharlasData). */
class ListarCharlasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provincia' => ['nullable', 'string', 'max:100'],
            'municipio' => ['nullable', 'string', 'max:100'],
            'ponente_id' => ['nullable', 'integer', 'exists:ponentes,id'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'solo_con_plazas' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:100'],
            'pagina' => ['nullable', 'integer', 'min:1'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:'.FiltroCharlasData::POR_PAGINA_MAXIMO],
        ];
    }
}
