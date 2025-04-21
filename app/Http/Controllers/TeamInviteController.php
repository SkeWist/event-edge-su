<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamInvite\SendInviteRequest;
use App\Http\Requests\TeamInvite\RespondInviteRequest;
use App\Models\Team;
use App\Models\TeamInvite;
use App\Models\User;
use App\Notifications\TeamInviteNotification;
use App\Notifications\TeamInviteResponseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class TeamInviteController extends Controller
{
    /**
     * Отправка приглашения в команду
     */
    public function sendInvite(SendInviteRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Проверяем, существует ли уже приглашение
        $existingInvite = TeamInvite::where('team_id', $validated['team_id'])
            ->where('user_id', $validated['user_id'])
            ->whereIn('status', ['pending'])
            ->first();

        if ($existingInvite) {
            return response()->json([
                'error' => 'Пользователь уже был приглашён в эту команду'
            ], 422);
        }

        // Получаем команду
        $team = Team::findOrFail($validated['team_id']);

        // Создаём приглашение
        $invite = TeamInvite::create([
            'team_id' => $validated['team_id'],
            'user_id' => $validated['user_id'],
            'expires_at' => Carbon::parse($validated['expires_at']),
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
        ]);

        // Формируем сообщение
        $message = $validated['message'] ?: "Вы приглашены в команду: " . $team->name;

        // Находим пользователя и отправляем уведомление
        $user = User::findOrFail($validated['user_id']);
        $user->notify(new TeamInviteNotification($invite, $message));

        return response()->json([
            'message' => 'Приглашение успешно отправлено пользователю',
            'data' => $invite
        ], 201);
    }

    /**
     * Ответ на приглашение в команду
     */
    public function respondInvite(RespondInviteRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $invite = TeamInvite::findOrFail($validated['invite_id']);

        // Проверяем, что приглашение еще не было обработано
        if ($invite->status !== 'pending') {
            return response()->json([
                'error' => 'Приглашение уже было обработано'
            ], 422);
        }

        // Проверяем срок действия приглашения
        if (Carbon::parse($invite->expires_at)->isPast()) {
            return response()->json([
                'error' => 'Срок действия приглашения истек'
            ], 422);
        }

        // Обновляем статус приглашения
        $invite->status = $validated['response'];
        $invite->save();

        // Если приглашение принято, добавляем пользователя в команду
        if ($validated['response'] === 'accepted') {
            DB::table('team_user')->insert([
                'team_id' => $invite->team_id,
                'user_id' => $invite->user_id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Формируем сообщение для уведомления
        $message = $validated['response'] === 'accepted' 
            ? 'Вы приняли приглашение в команду' 
            : 'Вы отклонили приглашение в команду';

        // Отправляем уведомление
        auth()->user()->notify(new TeamInviteResponseNotification($invite, $message));

        return response()->json([
            'message' => 'Ваш ответ на приглашение успешно обновлен',
            'data' => $invite
        ]);
    }
}
