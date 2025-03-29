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

        // Получаем уведомления пользователя (без статуса)
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get(['id', 'message', 'created_at']); // Убрали 'status' из выборки

        // Отмечаем все непрочитанные уведомления как "прочитанные"
        Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json($notifications);
    }
}
