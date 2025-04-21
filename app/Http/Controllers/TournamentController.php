<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\GameMatch;
use App\Models\Participant;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentBasket;
use App\Models\User;
use App\Models\Game;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Tournament\StoreTournamentRequest;
use App\Http\Requests\Tournament\UpdateTournamentRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class TournamentController extends Controller
{
    //  Просмотр списка турниров
    public function index(): JsonResponse
    {
        $tournaments = Tournament::with(['game', 'stage', 'organizer'])->get();
        
        $tournaments->transform(function ($tournament) {
            return [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d H:i'),
                'views_count' => $tournament->views_count,
                'status' => $tournament->status,
                'status_name' => $this->getStatusName($tournament->status),
                'game' => $tournament->game,
                'stage' => $tournament->stage,
                'organizer' => $tournament->organizer,
                'image' => $tournament->image ? asset('storage/' . $tournament->image) : null,
                'created_at' => Carbon::parse($tournament->created_at)->format('Y-m-d H:i'),
                'updated_at' => Carbon::parse($tournament->updated_at)->format('Y-m-d H:i')
            ];
        });

        return response()->json($tournaments);
    }

    /**
     * Метод для получения читаемого названия статуса
     */
    private function getStatusName($status): string
    {
        return match ($status) {
            'pending' => 'Ожидается',
            'ongoing' => 'В процессе',
            'completed' => 'Завершен',
            'canceled' => 'Отменен',
            'registrationOpen' => 'Регистрация открыта',
            'registrationClosed' => 'Регистрация закрыта',
            default => 'Неизвестно',
        };
    }
    // Создание нового турнира
    public function store(StoreTournamentRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Убедимся, что статус установлен
        if (!isset($data['status']) || empty($data['status'])) {
            $data['status'] = 'pending';
        }

        // Устанавливаем user_id из аутентифицированного пользователя
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('tournaments', 'public');
            $data['image'] = $path;
        }

        // Преобразуем даты в формат с секундами перед сохранением
        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::parse($data['start_date'])->format('Y-m-d H:i:s');
        }
        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::parse($data['end_date'])->format('Y-m-d H:i:s');
        }

        $tournament = Tournament::create($data);
        $tournament->load(['game', 'stage', 'organizer']);

        return response()->json([
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d H:i'),
            'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d H:i'),
            'views_count' => $tournament->views_count,
            'status' => $tournament->status,
            'status_name' => $this->getStatusName($tournament->status),
            'game' => $tournament->game,
            'stage' => $tournament->stage,
            'organizer' => $tournament->organizer,
            'image' => $tournament->image ? asset('storage/' . $tournament->image) : null
        ], 201);
    }

    // Просмотр одного турнира
    public function show($id): JsonResponse
    {
        $tournament = Tournament::with(['game', 'stage', 'organizer'])->find($id);

        \Log::info('Tournament loaded with relations', ['tournament' => $tournament->toArray()]);

        return response()->json([
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d H:i'),
            'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d H:i'),
            'views_count' => $tournament->views_count,
            'status' => $tournament->status,
            'status_name' => $this->getStatusName($tournament->status),
            'game' => $tournament->game,
            'stage' => $tournament->stage,
            'organizer' => $tournament->organizer,
            'image' => $tournament->image ? asset('storage/' . $tournament->image) : null
        ]);
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
            'registrationOpen' => 'Регистрация открыта',
            'registrationClosed' => 'Регистрация закрыта',
        ];

        $tournaments->transform(function ($tournament) use ($statusNames) {
            return [
                'id' => $tournament->id, // ID турнира
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d H:i'),
                'views_count' => $tournament->views_count,
                'organizer' => $tournament->organizer->name ?? 'Неизвестный организатор', // Имя организатора
                'game' => $tournament->game->name ?? 'Неизвестная игра', // Имя игры
                'stage' => $tournament->stage->name ?? 'Без стадии', // Имя стадии
                'status_name' => $statusNames[$tournament->status] ?? 'Без статуса', // Перевод статуса
                'image' => $tournament->image ? asset('storage/' . $tournament->image) : null
            ];
        });

        return response()->json($tournaments);
    }
    // Обновление турнира
    public function update(UpdateTournamentRequest $request, $id): JsonResponse
    {
        try {
            \Log::info('Updating tournament', ['id' => $id, 'request_data' => $request->all()]);

            $data = $request->validated();
            \Log::info('Validated data', $data);

            // Проверяем существование турнира
            $tournament = Tournament::where('id', $id)->first();

            if (!$tournament) {
                \Log::error('Tournament not found', ['id' => $id]);
                return response()->json(['error' => 'Турнир не найден'], 404);
            }

            \Log::info('Found tournament', $tournament->toArray());

            if ($request->hasFile('image')) {
                // Удаляем старое изображение, если оно есть
                if ($tournament->image) {
                    Storage::disk('public')->delete($tournament->image);
                }
                $path = $request->file('image')->store('tournaments', 'public');
                $data['image'] = $path;
            }

            $tournament->update($data);
            \Log::info('Tournament after update', $tournament->toArray());

            // Обновляем модель и загружаем связи
            $tournament->refresh();
            $tournament->load(['game', 'stage', 'organizer']);

            \Log::info('Tournament after refresh', $tournament->toArray());

            $response = [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'description' => $tournament->description,
                'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d H:i'),
                'views_count' => $tournament->views_count,
                'status_name' => $this->getStatusName($tournament->status),
                'game' => $tournament->game,
                'stage' => $tournament->stage,
                'organizer' => $tournament->organizer,
                'image' => $tournament->image ? asset('storage/' . $tournament->image) : null
            ];

            \Log::info('Response', $response);

            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Update error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Не удалось обновить турнир: ' . $e->getMessage()], 500);
        }
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

        // Проверка, что матч уже привязан к какому-либо турниру
        $existingMatch = TournamentBasket::where('game_match_id', $request->game_match_id)->exists();

        if ($existingMatch) {
            return response()->json(['message' => 'Этот матч уже добавлен в другой турнир и не может быть повторно использован.'], 400);
        }

        // Создание записи в турнирной сетке
        TournamentBasket::create([
            'tournament_id' => $request->tournament_id,
            'game_match_id' => $request->game_match_id,
            'status' => $request->status,
            'winner_team_id' => null,
        ]);

        return response()->json([
            'message' => 'Матч успешно добавлен в турнирную сетку!',
        ]);
    }
    // Метод для обновления результата матча и определения победителя
    public function updateMatchResult(Request $request, $tournamentId, $matchId)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'result' => 'required|string', // Результат матча
            'status' => 'required|in:scheduled,in_progress,completed', // Статус матча
            'winner_team_id' => 'required|exists:teams,id', // Победитель матча
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Проверяем, что турнир существует и что это турнир с id = 1
        if ($tournamentId != 1) {
            return response()->json(['message' => 'Турнир не найден или ID турнира недействителен.'], 400);
        }

        // Находим матч в турнирной сетке для данного турнира
        $match = TournamentBasket::where('game_match_id', $matchId)
            ->where('tournament_id', $tournamentId) // Проверяем, что матч принадлежит нужному турниру
            ->first();

        if (!$match) {
            return response()->json(['message' => 'Матч не найден в турнирной сетке для данного турнира.'], 404);
        }

        // Обновление результата в таблице TournamentBaskets
        $match->update([
            'result' => $request->result, // Обновляем результат
            'status' => $request->status, // Обновляем статус
            'winner_team_id' => $request->winner_team_id, // Обновляем победителя
        ]);

        // Теперь обновляем результат в таблице game_matches
        $gameMatch = $match->gameMatch; // Получаем сам матч через связь

        // Обновляем поле result в таблице game_matches
        $gameMatch->update([
            'result' => $request->result, // Обновляем результат матча
            'status' => $request->status, // Обновляем статус
            'winner_team_id' => $request->winner_team_id, // Обновляем победителя
        ]);

        // Если статус "completed", получаем команды и победителя
        if ($request->status == 'completed') {
            // Получаем команды через отношения
            $teamA = $gameMatch->teamA; // Команда A (team_1)
            $teamB = $gameMatch->teamB; // Команда B (team_2)
            $winnerTeam = $gameMatch->winnerTeam; // Победитель

            // Присваиваем полученные значения
            $match->team_a = $teamA ? $teamA->name : null;
            $match->team_b = $teamB ? $teamB->name : null;
            $match->winner_team = $winnerTeam ? $winnerTeam->name : null;
        }

        // Возвращаем результат
        return response()->json([
            'message' => 'Результат матча обновлён',
            'match' => $match,
        ]);
    }
    public function getTournamentBasket($tournamentId)
    {
        // Загружаем турнир с его матчами и командами
        $tournament = Tournament::with([
            'baskets.gameMatch', // Загружаем сами матчи
            'baskets.gameMatch.teamA', // Команда A
            'baskets.gameMatch.teamB', // Команда B
            'baskets.gameMatch.winnerTeam', // Победитель
            'baskets.gameMatch.stage', // Этап турнира
        ])->findOrFail($tournamentId);

        // Формируем данные для отображения турнирной сетки
        $basketData = $tournament->baskets->map(function ($basket) {
            return [
                'id' => $basket->id,
                'tournament_id' => $basket->tournament_id,
                'game_match_id' => $basket->game_match_id,
                'status' => $basket->status,
                'result' => $basket->gameMatch->result,
                'team_a' => $basket->gameMatch->teamA ? $basket->gameMatch->teamA->name : null,
                'team_b' => $basket->gameMatch->teamB ? $basket->gameMatch->teamB->name : null,
                'winner_team' => $basket->gameMatch->winnerTeam ? $basket->gameMatch->winnerTeam->name : null,
                'created_at' => Carbon::parse($basket->created_at)->format('Y-m-d H:i'),
                'updated_at' => Carbon::parse($basket->updated_at)->format('Y-m-d H:i'),
                'game_match' => [
                    'id' => $basket->gameMatch->id,
                    'tournament_id' => $basket->gameMatch->tournament_id,
                    'team_1_id' => $basket->gameMatch->team_1_id,
                    'team_2_id' => $basket->gameMatch->team_2_id,
                    'match_date' => Carbon::parse($basket->gameMatch->match_date)->format('Y-m-d H:i'),
                    'status' => $basket->gameMatch->status,
                    'result' => $basket->gameMatch->result,
                    'stage_id' => $basket->gameMatch->stage_id,
                    'winner_team_id' => $basket->gameMatch->winner_team_id,
                    'created_at' => Carbon::parse($basket->gameMatch->created_at)->format('Y-m-d H:i'),
                    'updated_at' => Carbon::parse($basket->gameMatch->updated_at)->format('Y-m-d H:i'),
                ],
            ];
        });

        return response()->json($basketData);
    }
    public function updateBasketResults(Request $request)
    {
        $validated = $request->validate([
            'matches' => 'required|array',
            'matches.*.match_id' => 'required|exists:game_matches,id',
            'matches.*.winner_team_id' => 'required|exists:teams,id',
        ]);

        foreach ($validated['matches'] as $match) {
            GameMatch::where('id', $match['match_id'])->update(['winner_team_id' => $match['winner_team_id']]);
        }

        return response()->json(['message' => 'Результаты обновлены успешно'], 200);
    }
    public function removeMatchFromTournament($tournamentId, $matchId)
    {
        // Проверяем существование матча в сетке турнира
        $match = TournamentBasket::where('tournament_id', $tournamentId)
            ->where('game_match_id', $matchId)
            ->first();

        if (!$match) {
            return response()->json(['error' => 'Матч не найден в турнирной сетке'], 404);
        }

        // Удаляем матч из турнирной сетки
        $match->delete();

        return response()->json(['message' => 'Матч удален из турнирной сетки']);
    }
    public function createStage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'required|exists:tournaments,id',
            'stage_id' => 'required|integer|min:1',
            'matches' => 'required|array|min:1',
            'matches.*.team_1_id' => 'nullable|exists:teams,id',
            'matches.*.team_2_id' => 'nullable|exists:teams,id',
            'matches.*.winner_team_id' => 'nullable|exists:teams,id', // Обработка победителя, если есть
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Получаем турнир
        $tournament = Tournament::find($request->tournament_id);
        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден.'], 404);
        }

        // Обновляем stage_id турнира
        $newStageId = $request->stage_id + 1;
        $tournament->stage_id = $newStageId;

        // Если стадия 4 → турнир завершен
        if ($newStageId >= 4) {
            $tournament->status = 'completed';
        }

        $tournament->save(); // Сохраняем обновление

        // Получаем победителей предыдущей стадии (если есть)
        $previousWinners = collect($request->matches)
            ->pluck('winner_team_id')
            ->filter()
            ->values();

        \Log::info('Создание новой стадии', [
            'tournament_id' => $request->tournament_id,
            'stage_id' => $request->stage_id,
            'matches' => $request->matches,
            'previousWinners' => $previousWinners->toArray(),
        ]);

        // Если предыдущие победители есть, но их меньше двух — возвращаем ошибку
        if ($previousWinners->count() < 2 && !$request->matches) {
            return response()->json(['error' => 'Недостаточно команд для следующей стадии.'], 400);
        }

        // Если предыдущих победителей нет, значит, это первая стадия
        $matches = [];
        if ($previousWinners->count() > 0) {
            // Создаем матчи из победителей предыдущей стадии
            for ($i = 0; $i < count($previousWinners); $i += 2) {
                $match = GameMatch::create([
                    'tournament_id' => $request->tournament_id,
                    'stage_id' => $newStageId,
                    'team_1_id' => $previousWinners[$i],
                    'team_2_id' => $previousWinners[$i + 1] ?? null,
                    'winner_team_id' => null,
                    'match_date' => Carbon::now()->format('Y-m-d H:i'),
                ]);

                // Добавляем id созданного матча в массив
                $matches[] = [
                    'game_match_id' => $match->id, // game_match_id
                    'team_1_id' => $match->team_1_id,
                    'team_2_id' => $match->team_2_id,
                ];
            }
        } else {
            // Создаем новые матчи на основе данных из запроса
            foreach ($request->matches as $matchData) {
                // Для каждого матча из запроса создаем новый матч
                $match = GameMatch::create([
                    'tournament_id' => $request->tournament_id,
                    'stage_id' => $newStageId,
                    'team_1_id' => $matchData['team_1_id'],
                    'team_2_id' => $matchData['team_2_id'],
                    'winner_team_id' => $matchData['winner_team_id'] ?? null, // Если победитель указан, передаем его
                    'match_date' => Carbon::now()->format('Y-m-d H:i'),
                ]);

                // Добавляем id созданного матча в массив
                $matches[] = [
                    'game_match_id' => $match->id, // game_match_id
                    'team_1_id' => $match->team_1_id,
                    'team_2_id' => $match->team_2_id,
                ];
            }
        }

        return response()->json(['message' => 'Стадия создана', 'matches' => $matches], 201);
    }
    public function updateTournamentStatus(Request $request, $tournamentId)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ongoing,completed,registrationOpen,registrationClose',
        ]);

        $tournament = Tournament::findOrFail($tournamentId);
        $tournament->update(['status' => $validated['status']]);

        // Определяем текст уведомления
        $message = match ($validated['status']) {
            'pending' => 'Турнир "' . $tournament->name . '" вот-вот начнётся!',
            'ongoing' => 'Турнир "' . $tournament->name . '" начался!',
            'canceled' => 'Турнир "' . $tournament->name . '" был отменён.',
            'completed' => 'Турнир "' . $tournament->name . '" завершён!',
        };

        // Рассылаем уведомления всем пользователям
        NotificationHelper::sendNotificationToAll($message);

        return response()->json(['message' => 'Статус турнира обновлён и уведомления отправлены.']);
    }
    public function getStatistics()
    {
        $statistics = [
            'tournaments_count' => Tournament::count(),
            'players_count' => Participant::count(), // Если игроки у тебя хранятся в Team
            'matches_count' => GameMatch::count(),
        ];

        return response()->json($statistics);
    }
    // Удаление турнира
    public function destroy(Tournament $tournament): JsonResponse
    {
        if ($tournament->image) {
            Storage::disk('public')->delete($tournament->image);
        }

        $tournament->delete();
        return response()->json(null, 204);
    }
    public function myTournaments(Request $request)
    {
        $user = auth()->user();

        // Только для админа и организатора
        if (!in_array($user->role_id, [1, 3])) {
            return response()->json(['error' => 'Доступ запрещён. Только для организаторов и админов.'], 403);
        }

        $now = Carbon::now()->format('Y-m-d H:i');

        // Получаем турниры, созданные пользователем
        $tournamentsQuery = Tournament::where('user_id', $user->id);

        // Прошедшие турниры
        $pastTournaments = (clone $tournamentsQuery)
            ->where('end_date', '<', $now)
            ->orderBy('end_date', 'desc')
            ->get();

        // Активные или будущие турниры
        $upcomingTournaments = (clone $tournamentsQuery)
            ->where('end_date', '>=', $now)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'past_tournaments' => $pastTournaments,
            'upcoming_tournaments' => $upcomingTournaments,
        ]);
    }
    // Метод для получения списка команд, участвующих в турнире
    public function getTournamentTeams($id)
    {
        // Находим турнир
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден.'], 404);
        }

        // Получаем команды из JSON поля teams
        $teams = json_decode($tournament->teams, true) ?? [];

        // Если команды есть, получаем их полную информацию
        if (!empty($teams)) {
            // Преобразуем вложенные массивы в плоский массив ID
            $teamIds = collect($teams)->flatten()->unique()->values()->all();
            $teams = Team::whereIn('id', $teamIds)->get();
        }

        return response()->json([
            'tournament_id' => $tournament->id,
            'tournament_name' => $tournament->name,
            'teams' => $teams
        ], 200);
    }
}
