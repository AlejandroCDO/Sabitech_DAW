<?php

namespace App\Api\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'        => ['required', 'string', 'max:50'],
            'provincia'     => ['required', 'string', 'max:100'],
            'municipio'     => ['required', 'string', 'max:100'],
            'calle'         => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'max:10'],
            'numero'        => ['required', 'string', 'max:10'],
            'puerta'        => ['nullable', 'string', 'max:10'],
        ];
    }
}
