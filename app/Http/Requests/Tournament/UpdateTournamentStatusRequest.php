<?php

namespace App\Http\Requests\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTournamentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,ongoing,completed,registrationOpen,registrationClose',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Необходимо указать статус турнира',
            'status.in' => 'Недопустимый статус турнира',
        ];
    }
} 