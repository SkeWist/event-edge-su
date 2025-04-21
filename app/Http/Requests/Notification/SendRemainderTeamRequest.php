<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class SendRemainderTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reminder_time' => 'required|integer|min:1',
            'unit' => 'required|in:minutes,hours',
        ];
    }

    public function messages(): array
    {
        return [
            'reminder_time.required' => 'Необходимо указать время напоминания',
            'reminder_time.integer' => 'Время должно быть целым числом',
            'reminder_time.min' => 'Минимальное время напоминания - 1',
            'unit.required' => 'Необходимо указать единицу измерения времени',
            'unit.in' => 'Единица измерения может быть только minutes или hours',
        ];
    }
} 