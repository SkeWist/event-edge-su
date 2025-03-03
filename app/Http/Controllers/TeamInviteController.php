<?php

namespace App\Http\Controllers;

use App\Models\TeamInvite;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamInviteController extends Controller
{
    /**
     * Просмотр списка приглашений для пользователя.
     */
    public function index(Request $request)
    {
        // Получаем все приглашения для текущего пользователя
        $userId = $request->user()->id;

        $invites = TeamInvite::where('user_id', $userId)->get();

        return response()->json($invites);
    }

    /**
     * Создание нового приглашения в команду.
     */
    public function store(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:users,id',
            'expires_at' => 'required|date|after:now', // Время истечения приглашения
        ]);

        // Создание нового приглашения
        $invite = TeamInvite::create([
            'team_id' => $request->input('team_id'),
            'user_id' => $request->input('user_id'),
            'expires_at' => $request->input('expires_at'),
            'status' => TeamInvite::STATUS_PENDING, // Статус "ожидает"
        ]);

        return response()->json([
            'message' => 'Приглашение успешно отправлено!',
            'invite' => $invite,
        ], 201);
    }
    /**
     * Принятие приглашения.
     */
    public function accept($inviteId)
    {
        // Поиск приглашения по ID
        $invite = TeamInvite::findOrFail($inviteId);

        // Обновляем статус на "принято"
        $invite->status = TeamInvite::STATUS_ACCEPTED;
        $invite->save();

        return response()->json([
            'message' => 'Приглашение принято!',
            'invite' => $invite,
        ]);
    }
    /**
     * Отклонение приглашения.
     */
    public function decline($inviteId)
    {
        // Поиск приглашения по ID
        $invite = TeamInvite::findOrFail($inviteId);

        // Обновляем статус на "отклонено"
        $invite->status = TeamInvite::STATUS_DECLINED;
        $invite->save();

        return response()->json([
            'message' => 'Приглашение отклонено!',
            'invite' => $invite,
        ]);
    }
    /**
     * Удаление приглашения.
     */
    public function destroy($inviteId)
    {
        // Поиск приглашения по ID
        $invite = TeamInvite::findOrFail($inviteId);

        // Удаление приглашения
        $invite->delete();

        return response()->json([
            'message' => 'Приглашение удалено!',
        ], 204);
    }
}
