<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $this->route('id'),
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Имя пользователя не может быть длиннее 255 символов',
            'email.email' => 'Неверный формат email',
            'email.unique' => 'Пользователь с таким email уже существует',
            'password.min' => 'Пароль должен быть не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'role_id.exists' => 'Указанная роль не существует',
        ];
    }
} 