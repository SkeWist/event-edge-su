<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Получение списка уведомлений для текущего пользователя.
     */
    public function index(Request $request)
    {
        // Получаем все уведомления для текущего пользователя
        $userId = $request->user()->id;
        $notifications = Notification::where('user_id', $userId)->get();

        return response()->json($notifications);
    }

    /**
     * Создание нового уведомления.
     */
    public function store(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:255',
        ]);

        // Создание нового уведомления
        $notification = Notification::create([
            'user_id' => $request->input('user_id'),
            'message' => $request->input('message'),
        ]);

        return response()->json([
            'message' => 'Уведомление успешно создано!',
            'notification' => $notification,
        ], 201);
    }

    /**
     * Просмотр уведомления по ID.
     */
    public function show($id)
    {
        // Поиск уведомления по ID
        $notification = Notification::findOrFail($id);

        return response()->json($notification);
    }

    /**
     * Обновление уведомления.
     */
    public function update(Request $request, $id)
    {
        // Валидация входных данных
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        // Поиск уведомления по ID
        $notification = Notification::findOrFail($id);

        // Обновление уведомления
        $notification->update([
            'message' => $request->input('message'),
        ]);

        return response()->json([
            'message' => 'Уведомление успешно обновлено!',
            'notification' => $notification,
        ]);
    }

    /**
     * Удаление уведомления.
     */
    public function destroy($id)
    {
        // Поиск уведомления по ID
        $notification = Notification::findOrFail($id);

        // Удаление уведомления
        $notification->delete();

        return response()->json([
            'message' => 'Уведомление удалено!',
        ], 204);
    }
}
