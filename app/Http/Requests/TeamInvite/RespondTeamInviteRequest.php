<?php

namespace App\Http\Requests\TeamInvite;

use Illuminate\Foundation\Http\FormRequest;

class RespondTeamInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invite_id' => 'required|exists:team_invites,id',
            'response' => 'required|in:accepted,rejected',
        ];
    }

    public function messages(): array
    {
        return [
            'invite_id.required' => 'Необходимо указать приглашение',
            'invite_id.exists' => 'Указанное приглашение не существует',
            'response.required' => 'Необходимо указать ответ на приглашение',
            'response.in' => 'Ответ может быть только: принято или отклонено',
        ];
    }
} 