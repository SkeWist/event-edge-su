<?php

namespace App\Http\Requests\StageType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStageTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать название типа этапа',
            'name.max' => 'Название типа этапа не может быть длиннее 255 символов',
        ];
    }
} 