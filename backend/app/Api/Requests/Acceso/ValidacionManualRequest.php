<?php

namespace App\Api\Requests\Acceso;

use Illuminate\Foundation\Http\FormRequest;

/** Registra la asistencia sin QR, cuando el asistente no puede mostrar su código. */
class ValidacionManualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'charla_id' => ['required', 'integer', 'exists:charlas,id'],
        ];
    }
}
