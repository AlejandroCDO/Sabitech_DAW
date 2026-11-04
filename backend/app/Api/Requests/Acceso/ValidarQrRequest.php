<?php

namespace App\Api\Requests\Acceso;

use Illuminate\Foundation\Http\FormRequest;

/** Registra la asistencia a partir del QR que muestra el asistente. */
class ValidarQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qr_token' => ['required', 'string', 'max:128'],   // cabe en asistencias.qr_token (varchar 128)
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ];
    }
}
