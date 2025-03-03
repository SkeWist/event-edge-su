<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        // Тут можно добавить проверку прав, если нужно
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
    public function messages()
 {
        return [
            'name.required' => 'Поле "Имя" обязательно для заполнения. Пожалуйста, укажите ваше имя.',
            'name.string' => 'Поле "Имя" должно содержать только буквы и символы. Пожалуйста, исправьте введённое значение.',
            'name.max' => 'Имя не может быть длиннее 255 символов. Пожалуйста, укажите более короткое имя.',
            
            'email.required' => 'Поле "Электронная почта" обязательно для заполнения. Пожалуйста, укажите ваш email.',
            'email.string' => 'Поле "Электронная почта" должно содержать только буквы и символы. Пожалуйста, исправьте введённый email.',
            'email.email' => 'Пожалуйста, введите корректный email. Например, example@domain.com.',
            'email.unique' => 'Этот email уже зарегистрирован. Попробуйте войти или используйте другой email.',
            
            'password.required' => 'Поле "Пароль" обязательно для заполнения. Пожалуйста, укажите ваш пароль.',
            'password.string' => 'Пароль должен состоять только из букв и цифр. Убедитесь, что введено правильное значение.',
            'password.min' => 'Пароль должен содержать минимум 8 символов. Пожалуйста, выберите более длинный пароль для безопасности.',
            'password.confirmed' => 'Пароли не совпадают. Пожалуйста, убедитесь, что оба пароля одинаковы.',
        ];
    }
}
