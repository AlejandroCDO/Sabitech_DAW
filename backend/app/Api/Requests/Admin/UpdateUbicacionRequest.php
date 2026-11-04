<?php

namespace App\Api\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'        => ['sometimes', 'required', 'string', 'max:50'],
            'provincia'     => ['sometimes', 'required', 'string', 'max:100'],
            'municipio'     => ['sometimes', 'required', 'string', 'max:100'],
            'calle'         => ['sometimes', 'required', 'string', 'max:100'],
            'codigo_postal' => ['sometimes', 'required', 'string', 'max:10'],
            'numero'        => ['sometimes', 'required', 'string', 'max:10'],
            'puerta'        => ['sometimes', 'nullable', 'string', 'max:10'],
        ];
    }
}