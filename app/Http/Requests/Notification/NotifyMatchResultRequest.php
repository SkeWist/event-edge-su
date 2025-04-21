<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotifyMatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'winner_team_id' => 'required|exists:teams,id',
            'result' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'winner_team_id.required' => 'Необходимо указать команду-победителя',
            'winner_team_id.exists' => 'Указанная команда не существует',
            'result.required' => 'Необходимо указать результат матча',
        ];
    }
} 