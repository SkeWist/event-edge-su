<?php

namespace App\Http\Requests\Ban;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'banned_until' => ['nullable', 'date', 'after:now'],
            'is_permanent' => ['nullable', Rule::in([true, false, 1, 0, '1', '0', 'true', 'false'])],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Причина бана обязательна',
            'reason.max' => 'Причина бана не может быть длиннее 1000 символов',
            'banned_until.date' => 'Некорректный формат даты',
            'banned_until.after' => 'Дата окончания бана должна быть в будущем',
            'is_permanent.in' => 'Некорректное значение для постоянного бана',
        ];
    }
} 