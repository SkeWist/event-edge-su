<?php

namespace App\Http\Requests\NewsFeed;

use App\Models\NewsFeed;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreNewsFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'status' => [
                'required',
                Rule::in([NewsFeed::STATUS_DRAFT, NewsFeed::STATUS_PUBLISHED, NewsFeed::STATUS_ARCHIVED]),
            ],
            'published_at' => 'nullable|date',
            'archived_at' => 'nullable|date|after:published_at',
            'category_id' => 'nullable|exists:news_categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            // Заголовок
            'title.required' => 'Поле "Заголовок" обязательно для заполнения',
            'title.string' => 'Заголовок должен быть строкой',
            'title.max' => 'Заголовок не может превышать 255 символов',

            // Описание
            'description.required' => 'Поле "Описание" обязательно для заполнения',
            'description.string' => 'Описание должно быть текстом',

            // Контент
            'content.required' => 'Поле "Содержание" обязательно для заполнения',
            'content.string' => 'Содержание должно быть текстом',

            // Статус
            'status.required' => 'Необходимо указать статус новости',
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
            'image.required' => 'Необходимо загрузить изображение',
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Допустимые форматы изображений: jpeg, png, jpg, gif',
            'image.max' => 'Изображение слишком большое. Максимальный размер: 2MB',

            //Дата
            'published_at.after' => 'Дата публикации не может быть в прошлом',

            // Мета-данные
            'meta_title.string' => 'Мета-заголовок должен быть строкой',
            'meta_title.max' => 'Мета-заголовок не может превышать 255 символов',
            'meta_description.string' => 'Мета-описание должно быть текстом',

            // Избранное
            'is_featured.boolean' => 'Поле "Избранное" должно быть true/false',
        ];
    }
}
