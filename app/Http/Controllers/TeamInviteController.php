<?php

namespace App\Http\Controllers;

use App\Models\TeamInvite;
use App\Models\Team;
use App\Models\User;
use App\Notifications\TeamInviteNotification;
use App\Notifications\TeamInviteResponseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TeamInviteController extends Controller
{
    public function sendInvite(Request $request)
    {
        // Валидация данных
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id', // Проверяем, что команда существует
            'user_id' => 'required|exists:users,id', // Проверяем, что пользователь существует
            'expires_at' => 'required|date|after:now', // Время, до которого действует приглашение
            'message' => 'nullable|string|max:255'// Кастомное сообщение
        ]);

        // Проверяем, существует ли уже приглашение для этого пользователя в эту команду
        $existingInvite = TeamInvite::where('team_id', $validated['team_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingInvite) {
            return response()->json(['message' => 'Пользователь уже был приглашён в эту команду.'], 400);
        }

        // Получаем команду
        $team = Team::find($validated['team_id']);

        // Создаём приглашение
        $invite = TeamInvite::create([
            'team_id' => $validated['team_id'],
            'user_id' => $validated['user_id'],
            'expires_at' => Carbon::parse($validated['expires_at']),
            'status' => 'pending', // Статус "ожидает"
            'message' => $validated['message'],
        ]);

        // Формируем сообщение, если не передано кастомное
        $message = $validated['message'] ?: "Вы приглашены в команду: " . $team->name; // Формируем сообщение с названием команды

        // Находим пользователя, которому отправляем приглашение
        $user = User::find($validated['user_id']);

        // Отправляем уведомление пользователю с дефолтным или кастомным сообщением
        $user->notify(new TeamInviteNotification($invite, $message)); // $message — это строка

        return response()->json([
            'message' => 'Приглашение успешно отправлено пользователю.',
            'invite' => $invite,
        ]);
    }
    public function respondInvite(Request $request)
    {
        // Валидация данных
        $validated = $request->validate([
            'invite_id' => 'required|exists:team_invites,id', // Проверяем, что приглашение существует
            'response' => 'required|in:accepted,rejected', // Проверяем, что ответ валиден
        ]);

        // Находим приглашение по ID
        $invite = TeamInvite::find($validated['invite_id']);

        // Проверяем, что приглашение еще не было отклонено или принято
        if ($invite->status != 'pending') {
            return response()->json(['message' => 'Приглашение уже было обработано.'], 400);
        }

        // Обновляем статус приглашения
        $invite->status = $validated['response'];
        $invite->save();

        // Находим пользователя, которому отправлено приглашение
        $user = User::find($invite->user_id);

        // Если приглашение принято, добавляем пользователя в команду
        if ($validated['response'] == 'accepted') {
            DB::table('team_user')->insert([
                'team_id' => $invite->team_id,
                'user_id' => $invite->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Отправляем уведомление о принятии или отклонении приглашения
        $message = $validated['response'] == 'accepted' ? 'Вы приняли приглашение в команду.' : 'Вы отклонили приглашение в команду.';
        $user->notify(new TeamInviteResponseNotification($invite, $message));

        return response()->json([
            'message' => 'Ваш ответ на приглашение успешно обновлен.',
            'invite' => $invite,
        ]);
    }
}
