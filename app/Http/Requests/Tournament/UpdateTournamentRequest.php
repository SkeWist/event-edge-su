<?php

namespace App\Http\Requests\Tournament;

use App\Models\Tournament;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'user_id' => 'nullable|exists:users,id',
            'game_id' => 'nullable|exists:games,id',
            'stage_id' => 'nullable|exists:stages,id',
            'status' => 'nullable|in:' . implode(',', array_keys(Tournament::getStatuses())),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192'
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Название турнира не может быть длиннее 255 символов',
            'start_date.date' => 'Неверный формат даты начала',
            'end_date.date' => 'Неверный формат даты окончания',
            'end_date.after' => 'Дата окончания должна быть позже даты начала',
            'user_id.exists' => 'Указанный пользователь не существует',
            'game_id.exists' => 'Указанная игра не существует',
            'stage_id.exists' => 'Указанный этап не существует',
            'status.in' => 'Недопустимый статус турнира',
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Допустимые форматы изображения: jpeg, png, jpg, gif',
            'image.max' => 'Размер изображения не должен превышать 8MB'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => $validator->errors()->first()
            ], 422)
        );
    }
} 