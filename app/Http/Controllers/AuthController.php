<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // password_confirmation должен быть передан
        ]);

        // Если валидация не прошла, возвращаем ошибки
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Создание нового пользователя с ролью по умолчанию
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 4, // Роль по умолчанию
        ]);

        // Генерация токена для аутентификации (если необходимо, используйте Sanction)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Ответ с токеном
        return response()->json([
            'message' => 'Вы успешно зарегистрировались!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'access_token' => $token,
            ]
        ], 201); // HTTP статус 201 — создано
    }

    public function login(Request $request)
    {
        // Валидация входных данных
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Пытаемся аутентифицировать пользователя
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Получаем пользователя по email
            $user = User::where('email', $request->email)->first();

            // Генерируем токен
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Вы успешно авторизовались!',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'access_token' => $token,
                ]
            ], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
    public function logout(Request $request)
    {
        // Отзываем токен текущего пользователя
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['message' => 'Вы успешно вышли из аккаунта!']);
    }
}
