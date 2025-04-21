<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Название игры не может быть длиннее 255 символов',
            'logo.image' => 'Файл должен быть изображением',
            'logo.mimes' => 'Допустимые форматы: jpg, png, jpeg, gif, svg',
            'logo.max' => 'Размер изображения не должен превышать 2MB',
        ];
    }
} 