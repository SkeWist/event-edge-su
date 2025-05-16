<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Регистрация нового пользователя
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 4,
        ]);

        return $this->generateTokens($user);
    }

    // Вход в систему
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
        return $this->generateTokens($user);
    }

    // Обновление Access токена по Refresh токену
    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token missing'], 401);
        }

        // Поиск пользователя по refresh токену
        $user = User::where('refresh_token', hash('sha256', $refreshToken))->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        return $this->generateTokens($user);
    }

    // Выход из системы
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->api_token = null;
            $user->refresh_token = null;
            $user->save();
        }

        // Удаляем куку refresh токена
        return response()->json(['message' => 'Вы успешно вышли из аккаунта!'])
            ->cookie('refresh_token', '', -1);
    }

    // Вспомогательная функция генерации Access и Refresh токенов
    private function generateTokens(User $user)
    {
        // Удаляем старые токены, если используем Sanctum
        $user->tokens()->delete();

        // Новый Access токен
        $accessToken = $user->createToken('auth_token')->plainTextToken;
        $cleanAccessToken = explode('|', $accessToken)[1] ?? $accessToken;

        // Новый Refresh токен
        $refreshToken = Str::random(60);

        // Сохраняем захэшированный refresh токен в БД
        $user->api_token = $cleanAccessToken;
        $user->refresh_token = hash('sha256', $refreshToken);
        $user->save();

        return response()->json([
            'access_token' => $cleanAccessToken,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? 'Пользователь',
            ]
        ])->cookie(
            'refresh_token', $refreshToken, 60 * 24 * 7, // 7 дней
            '/', null, true, true, false, 'Strict'
        );
    }
}
