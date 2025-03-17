<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Метод для обновления профиля пользователя
    public function updateProfile(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Проверка, что аватар — это изображение
            'password' => 'nullable|string|min:8|confirmed', // Обновление пароля (если передан)
        ]);

        // Если валидация не прошла
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Получаем текущего пользователя
        $user = Auth::user();

        // Обновляем только те данные, которые были переданы в запросе
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->hasFile('avatar')) {
            // Проверка наличия файла и его сохранение
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        if ($request->has('password')) {
            // Хешируем новый пароль
            $user->password = Hash::make($request->password);
        }

        // Сохраняем изменения
        $user->save();

        // Возвращаем обновлённые данные пользователя
        return response()->json([
            'id' => $user->id,
            'avatar' => asset('storage/' . $user->avatar), // Возвращаем полный путь к изображению
            'role' => $user->role->name, // Добавляем роль пользователя
        ]);
    }
}
