<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotifyTeamEliminationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.required' => 'Необходимо указать команду',
            'team_id.exists' => 'Указанная команда не существует',
        ];
    }
} 