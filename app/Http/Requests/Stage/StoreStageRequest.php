<?php

namespace App\Http\Requests\Stage;

use Illuminate\Foundation\Http\FormRequest;

class StoreStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'stage_type_id' => 'required|exists:stage_types,id',
            'rounds' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать название этапа',
            'name.max' => 'Название этапа не может быть длиннее 255 символов',
            'start_date.required' => 'Необходимо указать дату начала этапа',
            'start_date.date' => 'Неверный формат даты начала',
            'end_date.date' => 'Неверный формат даты окончания',
            'end_date.after' => 'Дата окончания должна быть позже даты начала',
            'stage_type_id.required' => 'Необходимо указать тип этапа',
            'stage_type_id.exists' => 'Указанный тип этапа не существует',
            'rounds.required' => 'Необходимо указать количество раундов',
        ];
    }
} 