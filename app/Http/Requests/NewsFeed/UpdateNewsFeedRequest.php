<?php

namespace App\Http\Requests\NewsFeed;

use App\Models\NewsFeed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'content' => 'sometimes|string',
            'status' => [
                'sometimes',
                Rule::in([NewsFeed::STATUS_DRAFT, NewsFeed::STATUS_PUBLISHED, NewsFeed::STATUS_ARCHIVED]),
            ],
            'published_at' => 'nullable|date',
            'archived_at' => 'nullable|date|after:published_at',
            'category_id' => 'nullable|exists:news_categories,id',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            // Заголовок
            'title.string' => 'Заголовок должен быть строкой',
            'title.max' => 'Заголовок не может превышать 255 символов',

            // Описание
            'description.string' => 'Описание должно быть текстом',

            // Контент
            'content.string' => 'Содержание должно быть текстом',

            // Статус
            'status.in' => 'Допустимые статусы: '.implode(', ', [
                    NewsFeed::STATUS_DRAFT,
                    NewsFeed::STATUS_PUBLISHED,
                    NewsFeed::STATUS_ARCHIVED
                ]),

            // Даты
            'published_at.date' => 'Некорректный формат даты публикации',
            'archived_at.date' => 'Некорректный формат даты архивации',
            'archived_at.after' => 'Дата архивации должна быть после даты публикации',

            // Категория
            'category_id.exists' => 'Выбранная категория не существует',

            // Изображение
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Допустимые форматы: jpeg, png, jpg, gif',
            'image.max' => 'Максимальный размер изображения: 2MB',

            // Мета-данные
            'meta_title.string' => 'Мета-заголовок должен быть строкой',
            'meta_title.max' => 'Мета-заголовок не может превышать 255 символов',
            'meta_description.string' => 'Мета-описание должно быть текстом',

            // Избранное
            'is_featured.boolean' => 'Поле "Избранное" должно быть логическим значением',

            // Автор
            'user_id.exists' => 'Указанный автор не существует'
        ];
    }
}
