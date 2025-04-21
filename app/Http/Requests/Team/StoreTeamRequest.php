<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:teams,name',
            'captain_id' => 'required|exists:users,id',
            'status' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Необходимо указать название команды',
            'name.max' => 'Название команды не может быть длиннее 255 символов',
            'name.unique' => 'Команда с таким названием уже существует',
            'captain_id.required' => 'Необходимо указать капитана команды',
            'captain_id.exists' => 'Указанный пользователь не существует',
            'status.required' => 'Необходимо указать статус команды',
            'status.max' => 'Статус команды не может быть длиннее 255 символов',
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