<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentBasket;
use App\Models\User;
use App\Models\Game;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{
    // Просмотр списка турниров
    public function index()
    {
        $tournaments = Tournament::with([
            'organizer:id,name',
            'game:id,name',
            'stage:id,name',
            'teams:id,name' // Добавляем id команд, чтобы избежать ошибки
        ])->get()->map(function ($tournament) {
            return [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
                'views_count' => $tournament->views_count,
                'status_name' => $this->getStatusName($tournament->status), // Название статуса
                'organizer' => $tournament->organizer,
                'game' => $tournament->game,
                'stage' => $tournament->stage,
                'teams' => $tournament->teams
            ];
        });

        return response()->json($tournaments);
    }

    /**
     * Метод для получения читаемого названия статуса
     */
    private function getStatusName($status)
    {
        return match ($status) {
            'pending' => 'Ожидается',
            'ongoing' => 'В процессе',
            'completed' => 'Завершен',
            default => 'Неизвестный статус',
        };
    }
    // Создание нового турнира
    public function store(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'game_id' => 'required|exists:games,id',
            'stage_id' => 'nullable|exists:stages,id',
            'status' => 'required|in:pending,ongoing,completed', // Проверка статуса
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id', // Проверка каждого идентификатора команды
        ]);

        // Если валидация не прошла
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Определяем пользователя по токену
        $userId = Auth::id(); // Получаем ID текущего аутентифицированного пользователя

        // Создаем турнир
        $tournament = Tournament::create(array_merge(
            $request->except('teams'), // Убираем teams из входящих данных
            ['views_count' => 0, 'user_id' => $userId] // Добавляем user_id
        ));

        // Если переданы команды, прикрепляем их
        if ($request->has('teams') && is_array($request->teams)) {
            $validTeams = Team::whereIn('id', $request->teams)->pluck('id')->toArray();

            if (count($validTeams) !== count($request->teams)) {
                return response()->json(['error' => 'Some teams are invalid.'], 400);
            }

            $tournament->teams()->attach($validTeams);
        }

        // Загружаем связанные данные
        $tournament->load('teams');

        // Добавляем статусное название
        $statusNames = [
            'pending' => 'Ожидание',
            'ongoing' => 'В процессе',
            'completed' => 'Завершен',
        ];

        $tournament->status_name = $statusNames[$tournament->status] ?? 'Неизвестно';

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

        $statusNames = [
            'pending' => 'Ожидание',
            'ongoing' => 'В процессе',
            'completed' => 'Завершен',
            'canceled' => 'Отменен',
        ];

        $tournaments->transform(function ($tournament) use ($statusNames) {
            return [
                'id' => $tournament->id, // ID турнира
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => $tournament->start_date,
                'end_date' => $tournament->end_date,
                'views_count' => $tournament->views_count,
                'organizer' => $tournament->organizer->name ?? 'Неизвестный организатор', // Имя организатора
                'game' => $tournament->game->name ?? 'Неизвестная игра', // Имя игры
                'stage' => $tournament->stage->name ?? 'Без стадии', // Имя стадии
                'status_name' => $statusNames[$tournament->status] ?? 'Без статуса', // Перевод статуса
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
            'status' => 'nullable|string|in:upcoming,ongoing,completed' // Добавляем валидацию для статуса
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $tournament = Tournament::findOrFail($id);
        $tournament->update($request->except('teams'));

        // Добавляем статус в ответе
        return response()->json([
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'start_date' => $tournament->start_date,
            'end_date' => $tournament->end_date,
            'views_count' => $tournament->views_count,
            'status_name' => $this->getStatusName($tournament->status),
            'organizer' => $tournament->organizer ? [
                'id' => $tournament->organizer->id,
                'name' => $tournament->organizer->name,
            ] : null,
            'game' => $tournament->game ? [
                'id' => $tournament->game->id,
                'name' => $tournament->game->name,
            ] : null,
            'stage' => $tournament->stage ? [
                'id' => $tournament->stage->id,
                'name' => $tournament->stage->name,
            ] : null,
            'teams' => $tournament->teams->map(fn($team) => [
                'id' => $team->id,
                'name' => $team->name,
            ]),
        ]);
    }
    public function addMatchToTournament(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|exists:tournaments,id', // Валидация tournament_id
            'game_match_id' => 'required|exists:game_matches,id',
            'status' => 'nullable|in:scheduled,in_progress,completed', // Статус матча
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Получаем tournament_id из запроса
        $tournamentId = $request->tournament_id;
        // Проверка, что матч не был уже добавлен в турнир
        $existingMatch = TournamentBasket::where('tournament_id', $tournamentId)
            ->where('game_match_id', $request->game_match_id)
            ->first();

        if ($existingMatch) {
            return response()->json(['message' => 'Этот матч уже добавлен в турнир.'], 400);
        }

        // Создание записи в турнирной сетке
        TournamentBasket::create([
            'tournament_id' => $request->tournament_id, // Должно быть передано из запроса
            'game_match_id' => $request->game_match_id,
            'status' => $request->status, // Используем статус вместо результата
            'winner_team_id' => null, // Победитель не указан на момент добавления
        ]);

        return response()->json([
            'message' => 'Матч успешно добавлен в турнирную сетку!',
        ]);
    }
    // Метод для обновления результата матча и определения победителя
    public function updateMatchResult(Request $request, $tournamentId, $matchId)
    {
        // Валидация данных
        $validated = $request->validate([
            'winner_team_id' => 'required|exists:teams,id', // Проверка на существование победителя
        ]);

        // Получаем турнир
        $tournament = Tournament::findOrFail($tournamentId);

        // Проверка, что матч существует в турнирной сетке
        $match = TournamentBasket::where('tournament_id', $tournamentId)
            ->where('game_match_id', $matchId)
            ->first();

        if (!$match) {
            return response()->json(['error' => 'Матч не найден в турнирной сетке'], 404);
        }

        // Обновляем результат матча в турнирной сетке
        $match->update([
            'winner_team_id' => $validated['winner_team_id'], // Обновляем победителя
            'status' => 'completed', // Обновляем статус на завершён
        ]);

        return response()->json(['message' => 'Результат матча обновлен'], 200);
    }
    // Метод для получения турнирной сетки
    public function getTournamentBasket($tournamentId)
    {
        $tournament = Tournament::with('baskets.teamA', 'baskets.teamB', 'baskets.winnerTeam')->findOrFail($tournamentId);

        return response()->json($tournament->baskets);
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
