<?php

namespace App\Http\Requests\NewsFeed;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'published_at' => 'nullable|date',
            'user_id' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Необходимо указать заголовок новости',
            'title.max' => 'Заголовок не может быть длиннее 255 символов',
            'description.required' => 'Необходимо указать описание новости',
            'status.required' => 'Необходимо указать статус новости',
            'published_at.date' => 'Неверный формат даты публикации',
            'user_id.required' => 'Необходимо указать автора новости',
            'user_id.exists' => 'Указанный автор не существует',
        ];
    }
} 