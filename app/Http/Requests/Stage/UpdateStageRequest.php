<?php

namespace App\Http\Requests\Stage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'stage_type_id' => 'nullable|exists:stage_types,id',
            'rounds' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Название этапа не может быть длиннее 255 символов',
            'start_date.date' => 'Неверный формат даты начала',
            'end_date.date' => 'Неверный формат даты окончания',
            'end_date.after' => 'Дата окончания должна быть позже даты начала',
            'stage_type_id.exists' => 'Указанный тип этапа не существует',
        ];
    }
} 