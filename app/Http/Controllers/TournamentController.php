<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\User;
use App\Models\Game;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{
    // Просмотр списка турниров
    public function index()
    {
        $tournaments = Tournament::with(['organizer', 'game', 'stage'])->get();

        $tournaments->transform(function ($tournament) {
            return [
                'id' => $tournament->id, // Видно ID турнира
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
                'views_count' => $tournament->views_count,
                'organizer' => $tournament->organizer->name ?? 'Неизвестный организатор', // Имя организатора
                'game_id' => $tournament->game_id, // Видно ID игры
                'game' => $tournament->game->name ?? 'Неизвестная игра', // Имя игры
                'stage' => $tournament->stage->name ?? 'Без стадии' // Стадия турнира
            ];
        });

        return response()->json($tournaments);
    }

    // Создание нового турнира
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'stage_id' => 'required|exists:stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Создаем новый турнир с дефолтным значением views_count
        $tournament = Tournament::create(array_merge($request->all(), ['views_count' => 0]));

        return response()->json([
            'message' => "Турнир '{$tournament->name}' успешно создан!",
            'tournament' => [
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
                'views_count' => $tournament->views_count,
                'organizer' => $tournament->organizer->name ?? 'Неизвестный организатор',
                'game' => $tournament->game->name ?? 'Неизвестная игра',
                'stage' => $tournament->stage->name ?? 'Без стадии'
            ]
        ], 201);
    }

    // Просмотр одного турнира
    public function show($id)
    {
        $tournament = Tournament::with(['organizer', 'game', 'stage'])->findOrFail($id);

        return response()->json([
            'id' => $tournament->id, // ID турнира
            'name' => $tournament->name,
            'description' => $tournament->description,
            'start_date' => $tournament->start_date,
            'end_date' => $tournament->end_date,
            'views_count' => $tournament->views_count,
            'organizer' => $tournament->organizer->name ?? 'Неизвестный организатор', // Имя организатора
            'game' => $tournament->game->name ?? 'Неизвестная игра', // Имя игры
            'stage' => $tournament->stage->name ?? 'Без стадии' // Имя стадии
        ]);
    }

    // Получение популярных турниров
    public function popularTournaments()
    {
        // Сортировка по количеству просмотров и получение топ-3 популярных турниров
        $tournaments = Tournament::orderByDesc('views_count')
            ->take(3)
            ->with(['organizer', 'game', 'stage']) // Подгружаем связи
            ->get();

        $tournaments->transform(function ($tournament) {
            return [
                'id' => $tournament->id, // ID турнира
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
                'views_count' => $tournament->views_count,
                'organizer' => $tournament->organizer->name ?? 'Неизвестный организатор', // Имя организатора
                'game' => $tournament->game->name ?? 'Неизвестная игра', // Имя игры
                'stage' => $tournament->stage->name ?? 'Без стадии' // Имя стадии
            ];
        });

        return response()->json($tournaments);
    }

    // Обновление турнира
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'user_id' => 'nullable|exists:users,id',
            'game_id' => 'nullable|exists:games,id',
            'stage_id' => 'nullable|exists:stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $tournament = Tournament::findOrFail($id);
        $tournament->update($request->only([
            'name', 'description', 'start_date', 'end_date', 'user_id', 'game_id', 'stage_id'
        ]));

        return response()->json([
            'message' => "Турнир '{$tournament->name}' успешно обновлен!",
            'tournament' => $tournament
        ]);
    }

    // Удаление турнира
    public function destroy($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournamentName = $tournament->name;
        $tournament->delete();

        return response()->json([
            'message' => "Турнир '{$tournamentName}' успешно удален!"
        ]);
    }
}
