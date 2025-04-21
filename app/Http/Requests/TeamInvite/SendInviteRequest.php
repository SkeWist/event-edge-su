<?php

namespace App\Http\Requests\TeamInvite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Team;

class SendInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $team = Team::find($this->team_id);
        
        if (!$team) {
            return false;
        }

        // Проверяем, является ли текущий пользователь капитаном команды
        return $team->captain_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:users,id',
            'expires_at' => 'required|date|after:now',
            'message' => 'nullable|string|max:255'
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
            'expires_at.after' => 'Срок действия приглашения должен быть больше текущей даты',
            'message.max' => 'Сообщение не может быть длиннее 255 символов'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => $validator->errors()->first()
            ], 422)
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'У вас нет прав для отправки приглашений в эту команду'
            ], 403)
        );
    }
} 