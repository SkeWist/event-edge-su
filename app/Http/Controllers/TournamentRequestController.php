<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TournamentRequestController extends Controller

{
  public function index()
  {
    // Получаем все заявки на турниры с пагинацией
    $tournamentRequests = TournamentRequest::with(['user', 'stageType'])
      ->orderBy('created_at', 'desc')
      ->get();

    // Форматируем данные для ответа
    $formattedRequests = $tournamentRequests->map(function ($request) {
      return [
        'id' => $request->id,
        'name' => $request->name,
        'description' => $request->description,
        'start_date' => Carbon::parse($request->start_date)->format('Y-m-d H:i'),
        'end_date' => Carbon::parse($request->end_date)->format('Y-m-d H:i'),
        'game_id' => $request->game_id,
        'game_name' => $request->game->name,
        'stage_type_id' => $request->stage_type_id,
        'stage_type_name' => $request->stageType->name ?? 'Тип не указан', // Используем правильное название отношения
        'status' => $request->status,
        'user_id' => $request->user_id,
        'user_name' => $request->user->name ?? 'Неизвестный пользователь',
        'image' => $request->image ? asset('storage/' . $request->image) : null,
        'teams' => $request->teams ? json_decode($request->teams) : null,
        'created_at' => Carbon::parse($request->created_at)->format('Y-m-d H:i'),
        'updated_at' => Carbon::parse($request->updated_at)->format('Y-m-d H:i'),
      ];
    });

    return response()->json([
      'tournament_requests' => $formattedRequests,
      'total' => $tournamentRequests->count()
    ], 200);
  }
    // Метод для отправки турнира на модерацию
    public function store(Request $request)
    {
        // Валидация данных запроса
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
            'game_id' => 'required|exists:games,id',
            'stage_type_id' => 'nullable|exists:stage_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192',
            'teams' => 'nullable|array',
            'teams.*' => 'integer',
        ], [
            'start_date.date_format' => 'Формат даты и времени должен быть Y-m-d H:i:s (например: 2025-04-20 15:00:00)',
            'end_date.date_format' => 'Формат даты и времени должен быть Y-m-d H:i:s (например: 2025-04-20 18:00:00)',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Проверяем, авторизован ли пользователь
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Пользователь не авторизован.'], 401);
        }

        // Инициализируем переменную для пути к изображению
        $imagePath = null;

        // Обработка изображения (если оно есть)
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
            'stage_type_id' => $request->stage_type_id,
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
                'start_date' => Carbon::parse($tournamentRequest->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($tournamentRequest->end_date)->format('Y-m-d H:i'),
                'game_id' => $tournamentRequest->game_id,
                'stage_type_id' => $tournamentRequest->stage_type_id,
                'status' => $tournamentRequest->status,
                'user_id' => $tournamentRequest->user_id,
                'image' => $tournamentRequest->image,
                'teams' => $tournamentRequest->teams
            ]),
        ]);

        return response()->json([
            'message' => 'Турнир отправлен на модерацию.',
            'tournament_request' => [
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'description' => $tournamentRequest->description,
                'start_date' => Carbon::parse($tournamentRequest->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($tournamentRequest->end_date)->format('Y-m-d H:i'),
                'game_id' => $tournamentRequest->game_id,
                'stage_type_id' => $tournamentRequest->stage_type_id,
                'status' => $tournamentRequest->status,
                'user_id' => $tournamentRequest->user_id,
                'image' => $tournamentRequest->image ? asset('storage/' . $tournamentRequest->image) : null,
                'teams' => $tournamentRequest->teams
            ]
        ], 201);
    }

    // Метод для отправки турнира на модерацию от пользователя
    public function storeUserRequest(Request $request)
    {
        // Валидация данных запроса
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'start_date' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
            'game_id' => 'required|exists:games,id',
            'stage_type_id' => 'nullable|exists:stage_types,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192',
            'teams' => 'nullable|array',
            'teams.*' => 'integer',
        ], [
            'start_date.date_format' => 'Формат даты и времени должен быть Y-m-d H:i:s (например: 2025-04-20 15:00:00)',
            'end_date.date_format' => 'Формат даты и времени должен быть Y-m-d H:i:s (например: 2025-04-20 18:00:00)',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Проверяем, авторизован ли пользователь
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['error' => 'Пользователь не авторизован.'], 401);
        }

        $imagePath = null;

        // Создаем запись в таблице tournament_requests
        $tournamentRequest = TournamentRequest::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'game_id' => $request->game_id,
            'stage_type_id' => $request->stage_type_id,
            'status' => 'pending',
            'user_id' => $userId,
            'image' => $imagePath,
            'teams' => $request->teams ? json_encode($request->teams) : null
        ]);

        // Уведомление админу о новом запросе
        Notification::create([
            'user_id' => 1, // Уведомление отправляется админу
            'message' => "Пользователь " . Auth::user()->name . " подал заявку на создание турнира: " . $request->name,
            'status' => 'unread',
            'data' => json_encode([
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'description' => $tournamentRequest->description,
                'start_date' => Carbon::parse($tournamentRequest->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($tournamentRequest->end_date)->format('Y-m-d H:i'),
                'game_id' => $tournamentRequest->game_id,
                'stage_type_id' => $tournamentRequest->stage_type_id,
                'status' => $tournamentRequest->status,
                'user_id' => $tournamentRequest->user_id,
                'image' => $tournamentRequest->image,
                'teams' => $tournamentRequest->teams
            ]),
        ]);

        return response()->json([
            'message' => 'Заявка на турнир отправлена на модерацию.',
            'tournament_request' => [
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'description' => $tournamentRequest->description,
                'start_date' => Carbon::parse($tournamentRequest->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($tournamentRequest->end_date)->format('Y-m-d H:i'),
                'game_id' => $tournamentRequest->game_id,
                'stage_type_id' => $tournamentRequest->stage_type_id,
                'status' => $tournamentRequest->status,
                'user_id' => $tournamentRequest->user_id,
                'image' => $tournamentRequest->image ? asset('storage/' . $tournamentRequest->image) : null,
                'teams' => $tournamentRequest->teams
            ]
        ], 201);
    }

    // Метод для принятия заявки пользователя (администратор)
    public function acceptRequestUser($id)
    {
        $tournamentRequest = TournamentRequest::find($id);

        if (!$tournamentRequest) {
            return response()->json(['error' => 'Заявка не найдена.'], 404);
        }

        // Меняем роль пользователя на организатора
        $user = User::find($tournamentRequest->user_id);
        $user->role_id = 3; // ID роли организатора
        $user->save();

        // Создаем турнир
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
            'teams' => $tournamentRequest->teams
        ]);

        // Обновляем статус заявки
        $tournamentRequest->update(['status' => 'approved']);

        // Уведомление пользователю о принятии заявки
        Notification::create([
            'user_id' => $tournamentRequest->user_id,
            'message' => "Ваша заявка на турнир '{$tournamentRequest->name}' принята. Теперь вы организатор.",
            'status' => 'unread',
            'data' => json_encode([
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'status' => 'approved',
                'tournament_id' => $tournament->id
            ]),
        ]);

        return response()->json([
            'message' => 'Заявка принята, пользователь назначен организатором.',
            'tournament_id' => $tournament->id
        ], 200);
    }

    // Метод для отклонения заявки пользователя (администратор)
    public function rejectRequestUser($id)
    {
        $tournamentRequest = TournamentRequest::find($id);

        if (!$tournamentRequest) {
            return response()->json(['error' => 'Заявка не найдена.'], 404);
        }

        // Обновляем статус заявки
        $tournamentRequest->update(['status' => 'rejected']);

        // Уведомление пользователю об отклонении заявки
        Notification::create([
            'user_id' => $tournamentRequest->user_id,
            'message' => "Ваша заявка на турнир '{$tournamentRequest->name}' отклонена.",
            'status' => 'unread',
            'data' => json_encode([
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'status' => 'rejected'
            ]),
        ]);

        return response()->json(['message' => 'Заявка отклонена.'], 200);
    }

    // Метод для принятия турнира организатора (администратор)
    public function acceptRequest($id)
    {
        $tournamentRequest = TournamentRequest::find($id);

        if (!$tournamentRequest) {
            return response()->json(['error' => 'Заявка не найдена.'], 404);
        }

        // Создаем турнир
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
            'teams' => $tournamentRequest->teams
        ]);

        // Обновляем статус заявки
        $tournamentRequest->update(['status' => 'approved']);

        // Уведомление организатору о принятии турнира
        Notification::create([
            'user_id' => $tournamentRequest->user_id,
            'message' => "Ваш турнир '{$tournamentRequest->name}' принят.",
            'status' => 'unread',
            'data' => json_encode([
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'status' => 'approved',
                'tournament_id' => $tournament->id
            ]),
        ]);

        return response()->json([
            'message' => 'Турнир принят.',
            'tournament_id' => $tournament->id
        ], 200);
    }

    // Метод для отклонения турнира организатора (администратор)
    public function rejectRequest($id)
    {
        $tournamentRequest = TournamentRequest::find($id);

        if (!$tournamentRequest) {
            return response()->json(['error' => 'Заявка не найдена.'], 404);
        }

        // Обновляем статус заявки
        $tournamentRequest->update(['status' => 'rejected']);

        // Уведомление организатору об отклонении турнира
        Notification::create([
            'user_id' => $tournamentRequest->user_id,
            'message' => "Ваш турнир '{$tournamentRequest->name}' отклонен.",
            'status' => 'unread',
            'data' => json_encode([
                'id' => $tournamentRequest->id,
                'name' => $tournamentRequest->name,
                'status' => 'rejected'
            ]),
        ]);

        return response()->json(['message' => 'Турнир отклонен.'], 200);
    }
}
