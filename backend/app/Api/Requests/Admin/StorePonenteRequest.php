<?php

namespace App\Api\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePonenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'biografia' => ['nullable', 'string'],
            'foto_url' => ['nullable', 'url:http,https', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }
}
