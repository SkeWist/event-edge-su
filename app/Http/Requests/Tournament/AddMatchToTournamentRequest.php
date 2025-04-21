<?php

namespace App\Http\Requests\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class AddMatchToTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tournament_id' => 'required|exists:tournaments,id',
            'game_match_id' => 'required|exists:game_matches,id',
            'status' => 'nullable|in:scheduled,in_progress,completed',
        ];
    }

    public function messages(): array
    {
        return [
            'tournament_id.required' => 'Необходимо указать турнир',
            'tournament_id.exists' => 'Указанный турнир не существует',
            'game_match_id.required' => 'Необходимо указать матч',
            'game_match_id.exists' => 'Указанный матч не существует',
            'status.in' => 'Недопустимый статус матча',
        ];
    }
} 