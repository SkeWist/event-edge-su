<?php

namespace App\Http\Requests\GameMatch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'tournament_id' => 'nullable|exists:tournaments,id',
            'team_1_id' => 'nullable|exists:teams,id',
            'team_2_id' => 'nullable|exists:teams,id',
            'match_date' => 'nullable|date_format:Y-m-d H:i:s',
            'stage_id' => 'nullable|exists:stages,id',
            'status' => 'required|in:pending,completed,canceled',
            'result' => 'nullable|string',
            'winner_team_id' => 'nullable|exists:teams,id',
        ];

        // Если статус completed, то результат и победитель обязательны
        if ($this->input('status') === 'completed') {
            $rules['result'] = 'required|string';
            $rules['winner_team_id'] = 'required|exists:teams,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tournament_id.exists' => 'Указанный турнир не существует',
            'team_1_id.exists' => 'Первая команда не существует',
            'team_2_id.exists' => 'Вторая команда не существует',
            'match_date.date_format' => 'Неверный формат даты и времени',
            'stage_id.exists' => 'Указанный этап не существует',
            'status.required' => 'Необходимо указать статус матча',
            'status.in' => 'Недопустимый статус матча',
            'result.required' => 'Необходимо указать результат матча',
            'result.string' => 'Результат матча должен быть строкой',
            'winner_team_id.required' => 'Необходимо указать команду-победителя',
            'winner_team_id.exists' => 'Указанная команда не существует',
        ];
    }
} 