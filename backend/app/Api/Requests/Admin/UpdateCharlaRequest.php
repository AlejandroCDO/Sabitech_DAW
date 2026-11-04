<?php

namespace App\Api\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCharlaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'ponente_id'   => ['sometimes', 'required', 'integer', 'exists:ponentes,id'],
            'ubicacion_id' => ['sometimes', 'required', 'integer', 'exists:ubicaciones,id'],
            'titulo'       => ['sometimes', 'required', 'string', 'max:100'],
            'descripcion'  => ['sometimes', 'required', 'string'],
            'inicio'       => ['sometimes', 'required', 'date'],
            'fin'          => ['sometimes', 'required', 'date'],
            'aforo'        => ['sometimes', 'required', 'integer', 'min:1', 'max:2147483647'],      // CHECK (aforo > 0)
        ];

        // CHECK (fin > inicio): solo se compara aquí si llegan los dos campos.
        // Si llega uno solo, CharlaService::actualizar() lo compara con el valor guardado.
        if ($this->has('inicio')) {
            $rules['fin'][] = 'after:inicio';
        }

        return $rules;
    }
}