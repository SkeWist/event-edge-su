<?php

namespace App\Http\Requests\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamToTournamentRequest extends FormRequest
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