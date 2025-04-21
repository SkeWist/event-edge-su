<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:8192',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Имя пользователя не может быть длиннее 255 символов',
            'email.email' => 'Неверный формат email',
            'email.unique' => 'Пользователь с таким email уже существует',
            'avatar.image' => 'Файл должен быть изображением',
            'avatar.mimes' => 'Допустимые форматы: jpeg, png, jpg, gif, svg',
            'avatar.max' => 'Размер изображения не должен превышать 8MB',
            'password.min' => 'Пароль должен быть не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
} 