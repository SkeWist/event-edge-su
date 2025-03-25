<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    public static function sendNotification($userId, $message)
    {
        Notification::create([
            'user_id' => $userId,
            'message' => $message,
        ]);
    }

    public static function sendNotificationToAll($message)
    {
        $users = User::pluck('id'); // Получаем всех пользователей
        foreach ($users as $userId) {
            self::sendNotification($userId, $message);
        }
    }
}
