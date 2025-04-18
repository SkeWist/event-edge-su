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
                'teams' => $tournament->teams,
                'image' => $tournament->image
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
            'registrationOpen' => 'Регистрация открыта',
            'registrationClosed' => 'Регистрация закрыта',
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
            'status' => 'nullable|in:pending,ongoing,completed,canceled,registrationOpen,registrationClosed', // nullable
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        Log::info('Запрос на создание турнира', $request->all());

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if ($file->isValid()) {
                $imagePath = $file->store('tournament_images', 'public');
                Log::info('Файл успешно загружен', ['path' => $imagePath]);
            } else {
                Log::error('Ошибка загрузки изображения');
                return response()->json(['error' => 'Ошибка загрузки изображения'], 400);
            }
        } else {
            Log::warning('Файл изображения отсутствует в запросе');
        }

        $userId = Auth::id();

        $tournament = new Tournament();
        $tournament->name = $request->name;
        $tournament->description = $request->description;
        $tournament->start_date = $request->start_date;
        $tournament->end_date = $request->end_date;
        $tournament->game_id = $request->game_id;
        $tournament->stage_id = $request->stage_id;
        $tournament->status = $request->status ?? 'pending'; // 👈 дефолтное значение
        $tournament->views_count = 0;
        $tournament->user_id = $userId;
        $tournament->image = $imagePath;
        $tournament->save();

        if ($request->has('teams') && is_array($request->teams)) {
            $tournament->teams()->attach($request->teams);
        }

        $statusNames = [
            'pending' => 'Ожидание',
            'ongoing' => 'В процессе',
            'completed' => 'Завершен',
            'canceled' => 'Отменен',
            'registrationOpen' => 'Регистрация открыта',
            'registrationClosed' => 'Регистрация закрыта',
        ];

        return response()->json([
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'start_date' => $tournament->start_date,
            'end_date' => $tournament->end_date,
            'stage_id' => $tournament->stage_id,
            'views_count' => $tournament->views_count,
            'status_name' => $statusNames[$tournament->status] ?? 'Неизвестно',
            'image' => $imagePath ? asset('storage/' . $imagePath) : null,
            'teams' => $tournament->teams()->pluck('teams.id')
        ], 201);
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
            'registrationOpen' => 'Регистрация открыта',
            'registrationClosed' => 'Регистрация закрыта',
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
            'status' => 'nullable|string|in:upcoming,ongoing,completed,canceled,registrationOpen,registrationClosed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $tournament = Tournament::findOrFail($id);

        // Загрузка нового изображения (если оно передано)
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('tournament_images', 'public');
            $tournament->image = $imagePath;
        }

        $tournament->update($request->except('image'));

        // Добавляем статус в ответе
        return response()->json([
            'id' => $tournament->id,
            'name' => $tournament->name,
            'description' => $tournament->description,
            'start_date' => $tournament->start_date,
            'end_date' => $tournament->end_date,
            'views_count' => $tournament->views_count,
            'status_name' => $this->getStatusName($tournament->status),
            'image' => $imagePath ? asset('storage/' . $imagePath) : null,
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
                'created_at' => $basket->created_at,
                'updated_at' => $basket->updated_at,
                'game_match' => [
                    'id' => $basket->gameMatch->id,
                    'tournament_id' => $basket->gameMatch->tournament_id,
                    'team_1_id' => $basket->gameMatch->team_1_id,
                    'team_2_id' => $basket->gameMatch->team_2_id,
                    'match_date' => $basket->gameMatch->match_date,
                    'status' => $basket->gameMatch->status,
                    'result' => $basket->gameMatch->result,
                    'stage_id' => $basket->gameMatch->stage_id,
                    'winner_team_id' => $basket->gameMatch->winner_team_id,
                    'created_at' => $basket->gameMatch->created_at,
                    'updated_at' => $basket->gameMatch->updated_at,
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
                    'match_date' => now(),
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
                    'match_date' => now(),
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
            'status' => 'required|in:upcoming,ongoing,canceled,completed',
        ]);

        $tournament = Tournament::findOrFail($tournamentId);
        $tournament->update(['status' => $validated['status']]);

        // Определяем текст уведомления
        $message = match ($validated['status']) {
            'upcoming' => 'Турнир "' . $tournament->name . '" вот-вот начнётся!',
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
    public function destroy($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->teams()->detach(); // Удаляем связи перед удалением турнира
        $tournament->delete();

        return response()->json(['message' => 'Турнир удален']);
    }
    public function myTournaments(Request $request)
    {
        $user = auth()->user();

        // Только для админа и организатора
        if (!in_array($user->role_id, [1, 3])) {
            return response()->json(['error' => 'Доступ запрещён. Только для организаторов и админов.'], 403);
        }

        $now = Carbon::now();

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
}
