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
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Заголовок не может быть длиннее 255 символов',
            'published_at.date' => 'Неверный формат даты публикации',
            'user_id.exists' => 'Указанный автор не существует',
        ];
    }
} 