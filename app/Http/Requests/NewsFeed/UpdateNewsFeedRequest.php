<?php

namespace App\Http\Requests\NewsFeed;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'published_at' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:8192',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Заголовок не может быть длиннее 255 символов',
            'published_at.date' => 'Неверный формат даты публикации',
            'user_id.exists' => 'Указанный автор не существует',
            'title.required' => 'Необходимо указать заголовок новости',
            'description.required' => 'Необходимо указать описание новости',
            'status.required' => 'Необходимо указать статус новости',
            'published_at.date' => 'Неверный формат даты публикации',
            'user_id.required' => 'Необходимо указать автора новости',
            'user_id.exists' => 'Указанный автор не существует',
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Допустимые форматы изображения: jpeg, png, jpg, gif',
            'image.max' => 'Размер изображения не должен превышать 8MB'
        ];
    }
} 