<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'description' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название игры обязательно',
            'name.max' => 'Название игры не может быть длиннее 255 символов',
            'logo.required' => 'Логотип игры обязателен',
            'logo.image' => 'Файл должен быть изображением',
            'logo.mimes' => 'Допустимые форматы: jpg, png, jpeg, gif, svg',
            'logo.max' => 'Размер изображения не должен превышать 2MB',
            'description.required' => 'Описание игры обязательно',
        ];
    }
} 