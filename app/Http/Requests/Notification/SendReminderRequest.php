<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class SendReminderRequest extends FormRequest
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
            'reminder_time.integer' => 'Время напоминания должно быть целым числом',
            'reminder_time.min' => 'Время напоминания должно быть больше 0',
            'unit.required' => 'Необходимо указать единицу измерения времени',
            'unit.in' => 'Единица измерения должна быть minutes или hours',
        ];
    }
} 