<?php

namespace App\Http\Requests\TeamInvite;

use Illuminate\Foundation\Http\FormRequest;

class SendTeamInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:users,id',
            'expires_at' => 'required|date|after:now',
            'message' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.required' => 'Необходимо указать команду',
            'team_id.exists' => 'Указанная команда не существует',
            'user_id.required' => 'Необходимо указать пользователя',
            'user_id.exists' => 'Указанный пользователь не существует',
            'expires_at.required' => 'Необходимо указать срок действия приглашения',
            'expires_at.date' => 'Неверный формат даты',
            'expires_at.after' => 'Срок действия приглашения должен быть в будущем',
            'message.max' => 'Сообщение не может быть длиннее 255 символов',
        ];
    }
} 