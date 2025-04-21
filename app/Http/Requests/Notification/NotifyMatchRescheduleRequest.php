<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotifyMatchRescheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_time' => 'required|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'new_time.required' => 'Необходимо указать новое время матча',
            'new_time.date_format' => 'Неверный формат времени. Используйте формат HH:MM',
        ];
    }
} 