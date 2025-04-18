<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TournamentRequestController extends Controller
{
    // Метод для отправки турнира на модерацию
    public function store(Request $request)
    {
        // Валидация данных запроса
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'game_id' => 'required|integer',
            'stage_id' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'teams' => 'nullable|array',
            'teams.*' => 'integer',  // Если teams - это массив чисел
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Проверяем, авторизован ли пользователь
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Пользователь не авторизован.'], 401);
        }

        // Обработка изображения (если оно есть)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if ($file->isValid()) {
                $imagePath = $file->store('tournament_images', 'public');
            } else {
                return response()->json(['error' => 'Ошибка загрузки изображения.'], 400);
            }
        }

        // Создаем запись в таблице tournament_requests
        $tournamentRequest = TournamentRequest::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'game_id' => $request->game_id,
            'stage_id' => $request->stage_id,
            'status' => 'pending',
            'user_id' => $userId,
            'image' => $imagePath,
            'teams' => $request->teams ? json_encode($request->teams) : null
        ]);

        // Уведомление админу о новом запросе
        Notification::create([
            'user_id' => 1, // Уведомление отправляется админу
            'message' => "Организатор {$request->user()->name} подал турнир на модерацию: {$request->name}",
            'status' => 'unread',
            'data' => json_encode([
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'description' => $tournamentRequest->description,
                'start_date' => $tournamentRequest->start_date,
                'end_date' => $tournamentRequest->end_date,
                'game_id' => $tournamentRequest->game_id,
                'stage_id' => $tournamentRequest->stage_id,
                'status' => $tournamentRequest->status,
                'user_id' => $tournamentRequest->user_id,
                'image' => $tournamentRequest->image,
                'teams' => $tournamentRequest->teams
            ]),
        ]);

        return response()->json(['message' => 'Турнир отправлен на модерацию.'], 201);
    }

    // Метод для принятия турнира (администратор)
    public function acceptRequest($id)
    {
        $tournamentRequest = TournamentRequest::find($id);

        if (!$tournamentRequest) {
            return response()->json(['error' => 'Турнир не найден.'], 404);
        }

        // Создаем турнир в таблице tournaments
        $tournament = Tournament::create([
            'name' => $tournamentRequest->name,
            'description' => $tournamentRequest->description,
            'start_date' => $tournamentRequest->start_date,
            'end_date' => $tournamentRequest->end_date,
            'game_id' => $tournamentRequest->game_id,
            'stage_id' => $tournamentRequest->stage_id,
            'status' => 'pending',
            'user_id' => $tournamentRequest->user_id,
            'image' => $tournamentRequest->image,
            'teams' => $tournamentRequest->teams,
        ]);

        // Уведомление организатору о принятии турнира
        Notification::create([
            'user_id' => $tournamentRequest->user_id,
            'message' => "Ваш турнир '{$tournamentRequest->name}' принят и добавлен в систему.",
            'status' => 'unread',
            'data' => json_encode($tournamentRequest->only([
                'id', 'name', 'description', 'start_date', 'end_date',
                'game_id', 'stage_id', 'status', 'user_id', 'image', 'teams'
            ])),
            'type' => 'tournament_accepted', // Тип уведомления
        ]);

        // Удаляем запрос на турнир из таблицы tournament_requests
        $tournamentRequest->delete();

        return response()->json(['message' => 'Турнир принят и добавлен в систему.'], 200);
    }

    // Метод для отклонения турнира (администратор)
    public function rejectRequest($id)
    {
        $tournamentRequest = TournamentRequest::find($id);

        if (!$tournamentRequest) {
            return response()->json(['error' => 'Турнир не найден.'], 404);
        }

        // Уведомление организатору о отклонении турнира
        Notification::create([
            'user_id' => $tournamentRequest->user_id,
            'message' => "Ваш турнир '{$tournamentRequest->name}' был отклонен.",
            'status' => 'unread',
            'data' => json_encode($tournamentRequest->only([
                'id', 'name', 'description', 'start_date', 'end_date',
                'game_id', 'stage_id', 'status', 'user_id', 'image', 'teams'
            ])),
            'type' => 'tournament_rejected', // Тип уведомления
        ]);

        // Удаляем запрос на турнир из таблицы tournament_requests
        $tournamentRequest->delete();

        return response()->json(['message' => 'Турнир отклонен и удален из системы.'], 200);
    }
}
