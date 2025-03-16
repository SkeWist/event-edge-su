<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Генерируем токен один раз
        $token = Str::random(60);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 4,
            'api_token' => $token, // Сохраняем токен
        ]);

        return response()->json([
            'message' => 'Вы успешно зарегистрировались!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'access_token' => $token,
            ]
        ], 201);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Неверные учетные данные'], 401);
        }

        $user = Auth::user();

        // Удаляем старые токены перед созданием нового (если используем Sanctum)
        $user->tokens()->delete();

        // Создаем новый токен
        $token = $user->createToken('auth_token')->plainTextToken;

        // Убираем префикс `2|`
        $cleanToken = explode('|', $token)[1] ?? $token;

        // Сохраняем токен в базу данных
        $user->api_token = $cleanToken;
        $user->save();

        return response()->json([
            'message' => 'Успешный вход в систему!',
            'access_token' => $cleanToken,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? 'Пользователь',
            ]
        ]);
    }
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->api_token = null; // Очищаем токен
            $user->save();
        }

        return response()->json(['message' => 'Вы успешно вышли из аккаунта!']);
    }
}
