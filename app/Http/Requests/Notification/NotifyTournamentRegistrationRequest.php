<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotifyTournamentRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tournament_id' => 'required|exists:tournaments,id',
            'team_id' => 'required|exists:teams,id',
            'message' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'tournament_id.required' => 'Необходимо указать турнир',
            'tournament_id.exists' => 'Указанный турнир не существует',
            'team_id.required' => 'Необходимо указать команду',
            'team_id.exists' => 'Указанная команда не существует',
        ];
    }
}
