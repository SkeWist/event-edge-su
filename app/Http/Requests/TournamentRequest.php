<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TournamentRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь на выполнение запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации запроса.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'game_id' => 'required|exists:games,id',
            'stage_id' => 'nullable|exists:stages,id',
            'status' => 'nullable|in:pending,ongoing,completed,canceled,registrationOpen,registrationClosed',
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Поле "Название турнира" обязательно.',
            'name.string' => 'Название турнира должно быть строкой.',
            'name.max' => 'Название турнира не должно превышать 255 символов.',

            'description.string' => 'Описание должно быть строкой.',

            'start_date.required' => 'Дата начала обязательна.',
            'start_date.date' => 'Дата начала должна быть корректной датой.',

            'end_date.date' => 'Дата окончания должна быть корректной датой.',
            'end_date.after' => 'Дата окончания должна быть позже даты начала.',

            'game_id.required' => 'Выбор игры обязателен.',
            'game_id.exists' => 'Указанная игра не найдена.',

            'stage_id.exists' => 'Указанный этап не найден.',

            'status.in' => 'Неверный статус турнира.',

            'teams.array' => 'Команды должны быть переданы в виде массива.',
            'teams.*.exists' => 'Одна или несколько выбранных команд не существуют.',

            'image.image' => 'Файл должен быть изображением.',
            'image.mimes' => 'Разрешены только форматы jpeg, png, jpg и gif.',
            'image.max' => 'Максимальный размер изображения — 2 МБ.',
        ];
    }
}
