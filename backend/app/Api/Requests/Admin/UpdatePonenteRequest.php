<?php

namespace App\Api\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePonenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'biografia' => ['sometimes', 'nullable', 'string'],
            'foto_url' => ['sometimes', 'nullable', 'url:http,https', 'max:255'],
            'especialidad' => ['sometimes', 'nullable', 'string', 'max:100'],
            'linkedin_url' => ['sometimes', 'nullable', 'url:http,https', 'max:255'],
        ];
    }
}
