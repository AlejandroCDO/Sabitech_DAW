<?php

namespace App\Api\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCharlaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ponente_id'   => ['required', 'integer', 'exists:ponentes,id'],
            'ubicacion_id' => ['required', 'integer', 'exists:ubicaciones,id'],
            'titulo'       => ['required', 'string', 'max:100'],
            'descripcion'  => ['required', 'string'],
            'inicio'       => ['required', 'date'],
            'fin'          => ['required', 'date', 'after:inicio'],   // CHECK (fin > inicio)
            'aforo'        => ['required', 'integer', 'min:1', 'max:2147483647'],      // CHECK (aforo > 0)
        ];
    }
}
