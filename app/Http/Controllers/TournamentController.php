<?php

namespace App\Http\Controllers;

use App\Models\Team;
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
        $tournaments = Tournament::with([
            'organizer:id,name', // Выбираем только id и name для организатора
            'game:id,name', // Выбираем только id и name для игры
            'stage:id,name', // Выбираем только id и name для стадии
            'teams:name' // Выбираем только id и name для команд
        ])->get();

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
            'stage_id' => 'nullable|exists:stages,id',
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id', // Проверка каждого идентификатора команды
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Создаем турнир без 'teams', так как они будут прикреплены позже
        $tournament = Tournament::create(array_merge($request->except('teams'), ['views_count' => 0]));

        // Если передан массив команд, прикрепляем их к турниру
        if ($request->has('teams') && is_array($request->teams)) {
            // Получаем только существующие команды
            $validTeams = Team::whereIn('id', $request->teams)->pluck('id')->toArray();

            if (count($validTeams) !== count($request->teams)) {
                return response()->json(['error' => 'Some teams are invalid.'], 400);
            }

            $tournament->teams()->attach($validTeams);
        }

        // Загружаем все связанные данные (например, команды)
        $tournament->load('teams');  // Если турнир имеет связь с командами

        return response()->json($tournament, 201);
    }
    // Просмотр одного турнира
    public function show($id)
    {
        $tournament = Tournament::with([
            'organizer:id,name',
            'game:id,name',
            'stage:id,name',
            'teams:name'
        ])->findOrFail($id);

        // Увеличиваем количество просмотров
        $tournament->increment('views_count');

        return response()->json($tournament);
    }

    public function addTeam(Request $request, $tournamentId)
    {
        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id', // Проверка на существование команды
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Находим турнир по tournamentId
        $tournament = Tournament::findOrFail($tournamentId);
        // Находим команду по teamId из запроса
        $team = Team::findOrFail($request->team_id);

        // Проверяем, не находится ли команда уже в другом турнире
        if ($team->tournaments->contains($tournament)) {
            return response()->json(['error' => 'Команда уже участвует в этом турнире.'], 400);
        }

        // Добавляем команду в турнир через промежуточную таблицу
        $tournament->teams()->attach($team->id);

        // Возвращаем сообщение с успешным добавлением команды в турнир
        return response()->json(['message' => "Команда '{$team->name}' добавлена в турнир '{$tournament->name}'!"]);
    }
    public function removeTeam($tournamentId, $teamId)
    {
        // Находим турнир по tournamentId
        $tournament = Tournament::findOrFail($tournamentId);

        // Находим команду по teamId
        $team = Team::findOrFail($teamId);

        // Проверяем, состоит ли команда в турнире
        if (!$tournament->teams->contains($team)) {
            return response()->json(['error' => 'Команда не найдена в этом турнире.'], 404);
        }

        // Удаляем команду из турнира через промежуточную таблицу
        $tournament->teams()->detach($team->id);

        // Возвращаем сообщение об успешном удалении
        return response()->json(['message' => "Команда '{$team->name}' удалена из турнира '{$tournament->name}'!"]);
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
            'stage_id' => 'nullable|exists:stages,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $tournament = Tournament::findOrFail($id);
        $tournament->update($request->except('teams'));

        return response()->json($tournament);
    }
    // Удаление турнира
    public function destroy($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->teams()->detach(); // Удаляем связи перед удалением турнира
        $tournament->delete();

        return response()->json(['message' => 'Турнир удален']);
    }
}
