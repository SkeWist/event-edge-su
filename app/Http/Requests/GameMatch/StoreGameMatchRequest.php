<?php

namespace App\Http\Requests\GameMatch;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tournament_id' => 'required|exists:tournaments,id',
            'team_1_id' => 'required|exists:teams,id',
            'team_2_id' => 'required|exists:teams,id',
            'match_date' => 'required|date_format:Y-m-d\TH:i',
            'stage_id' => 'nullable|exists:stages,id',
            'status' => 'required|in:pending,completed,canceled',
            'result' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tournament_id.required' => 'Необходимо указать турнир',
            'tournament_id.exists' => 'Указанный турнир не существует',
            'team_1_id.required' => 'Необходимо указать первую команду',
            'team_1_id.exists' => 'Первая команда не существует',
            'team_2_id.required' => 'Необходимо указать вторую команду',
            'team_2_id.exists' => 'Вторая команда не существует',
            'match_date.required' => 'Необходимо указать дату матча',
            'match_date.date_format' => 'Неверный формат даты и времени',
            'stage_id.exists' => 'Указанный этап не существует',
            'status.required' => 'Необходимо указать статус матча',
            'status.in' => 'Недопустимый статус матча',
            'result.string' => 'Результат матча должен быть строкой',
        ];
    }
} 