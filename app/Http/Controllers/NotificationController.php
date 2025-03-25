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

        // Получаем уведомления пользователя (последние 50)
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json($notifications);
    }
}
