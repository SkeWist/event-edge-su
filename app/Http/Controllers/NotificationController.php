<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getUserNotifications(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        // Получаем последние 50 уведомлений пользователя (с учетом статуса)
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get(['id', 'message', 'status', 'created_at']);

        // Считаем количество непрочитанных уведомлений
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount, // Добавлен счетчик
            'notifications' => $notifications
        ]);
    }
    public function getUnreadNotifications(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        // Получаем все непрочитанные уведомления пользователя
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'message', 'created_at']);

        // Отмечаем их как "прочитанные"
        Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json($unreadNotifications);
    }
}
