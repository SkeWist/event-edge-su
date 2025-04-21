<?php

namespace App\Http\Requests\TeamInvite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\TeamInvite;
use Illuminate\Support\Facades\Log;

class RespondInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inviteId = $this->input('invite_id');
        $userId = auth()->id();
        
        Log::info('RespondInviteRequest authorize check', [
            'invite_id' => $inviteId,
            'auth_user_id' => $userId,
            'request_data' => $this->all()
        ]);

        if (!$inviteId) {
            Log::info('No invite_id provided');
            return false;
        }

        $invite = TeamInvite::find($inviteId);
        
        if (!$invite) {
            Log::info('Invite not found');
            return false;
        }

        Log::info('Invite details', [
            'invite_user_id' => $invite->user_id,
            'auth_user_id' => $userId,
            'is_match' => $invite->user_id === $userId
        ]);

        return $invite->user_id === $userId;
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
            'response.in' => 'Недопустимый ответ на приглашение',
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
        $userId = auth()->id();
        $inviteId = $this->input('invite_id');
        $invite = TeamInvite::find($inviteId);

        $error = 'У вас нет прав для ответа на это приглашение';
        
        if (!auth()->check()) {
            $error = 'Необходимо авторизоваться';
        } elseif (!$invite) {
            $error = 'Приглашение не найдено';
        } elseif ($invite->user_id !== $userId) {
            $error = 'Это приглашение предназначено другому пользователю';
        }

        Log::info('Authorization failed', [
            'user_id' => $userId,
            'invite_id' => $inviteId,
            'invite_exists' => (bool)$invite,
            'invite_user_id' => $invite ? $invite->user_id : null,
            'error' => $error
        ]);

        throw new HttpResponseException(
            response()->json([
                'error' => $error
            ], 403)
        );
    }
} 