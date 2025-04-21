<?php

namespace App\Http\Requests\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMatchResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'result' => 'required|string',
            'status' => 'required|in:scheduled,in_progress,completed',
            'winner_team_id' => 'required|exists:teams,id',
        ];
    }

    public function messages(): array
    {
        return [
            'result.required' => 'Необходимо указать результат матча',
            'status.required' => 'Необходимо указать статус матча',
            'status.in' => 'Недопустимый статус матча',
            'winner_team_id.required' => 'Необходимо указать команду-победителя',
            'winner_team_id.exists' => 'Указанная команда не существует',
        ];
    }
} 