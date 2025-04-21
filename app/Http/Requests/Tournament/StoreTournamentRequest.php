<?php

namespace App\Http\Requests\Tournament;

use App\Models\Tournament;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'nullable|date_format:Y-m-d H:i:s|after:start_date',
            'game_id' => 'required|exists:games,id',
            'stage_id' => 'nullable|exists:stages,id',
            'status' => 'nullable|in:' . implode(',', array_keys(Tournament::getStatuses())),
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать название турнира',
            'name.max' => 'Название турнира не может быть длиннее 255 символов',
            'start_date.required' => 'Необходимо указать дату начала турнира',
            'start_date.date_format' => 'Формат даты и времени должен быть Y-m-d H:i:s (например: 2025-05-10 15:30:00)',
            'end_date.date_format' => 'Формат даты и времени должен быть Y-m-d H:i:s (например: 2025-05-10 18:30:00)',
            'end_date.after' => 'Дата окончания должна быть позже даты начала',
            'game_id.required' => 'Необходимо указать игру',
            'game_id.exists' => 'Указанная игра не существует',
            'stage_id.exists' => 'Указанный этап не существует',
            'status.in' => 'Недопустимый статус турнира',
            'teams.array' => 'Неверный формат списка команд',
            'teams.*.exists' => 'Одна или несколько команд не существуют',
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Допустимые форматы изображения: jpeg, png, jpg, gif',
            'image.max' => 'Размер изображения не должен превышать 8MB'
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('status') || empty($this->status)) {
            $this->merge(['status' => 'pending']);
        }
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