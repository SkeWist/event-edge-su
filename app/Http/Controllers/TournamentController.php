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
use App\Notifications\TournamentStatusNotification;
use Illuminate\Http\Request;
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
            'status' => 'required|in:pending,ongoing,completed',
            'teams' => 'nullable|array',
            'teams.*' => 'exists:teams,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Логируем факт получения запроса
        Log::info('Запрос на создание турнира', $request->all());

        // Обработка загрузки изображения
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

        // Определяем пользователя
        $userId = Auth::id();

        // Создаем турнир
        $tournament = new Tournament();
        $tournament->name = $request->name;
        $tournament->description = $request->description;
        $tournament->start_date = $request->start_date;
        $tournament->end_date = $request->end_date;
        $tournament->game_id = $request->game_id;
        $tournament->stage_id = $request->stage_id;
        $tournament->status = $request->status;
        $tournament->views_count = 0;
        $tournament->user_id = $userId;
        $tournament->image = $imagePath; // Записываем путь к изображению
        $tournament->save();

        // Добавляем статусное название
        $statusNames = [
            'pending' => 'Ожидание',
            'ongoing' => 'В процессе',
            'completed' => 'Завершен',
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
            'image' => $imagePath ? asset('storage/' . $imagePath) : null // Ссылка на изображение
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
            'status' => 'nullable|string|in:upcoming,ongoing,completed',
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
        $validated = $request->validate([
            'winner_team_id' => 'nullable|exists:teams,id',
            'status' => 'required|in:pending,completed,canceled',
            'result' => 'nullable|string'
        ]);

        // Получаем запись матча из game_matches
        $gameMatch = GameMatch::findOrFail($matchId);

        // Обновляем статус и результат в game_matches
        $updateData = [
            'status' => $validated['status'],
            'result' => $validated['result'] ?? $gameMatch->result,
        ];

        // Если матч завершён, устанавливаем победителя
        if ($validated['status'] === 'completed') {
            if (empty($validated['winner_team_id'])) {
                return response()->json(['error' => 'Для завершенного матча необходимо указать победителя'], 400);
            }
            $updateData['winner_team_id'] = $validated['winner_team_id'];
        }

        // Если матч отменён, обнуляем победителя
        if ($validated['status'] === 'canceled') {
            $updateData['winner_team_id'] = null;
        }

        $gameMatch->update($updateData);

        return response()->json([
            'message' => 'Результат матча обновлён',
            'match' => $gameMatch
        ], 200);
    }
    // Метод для получения турнирной сетки
    public function getTournamentBasket($tournamentId)
    {
        $tournament = Tournament::with('baskets.teamA', 'baskets.teamB', 'baskets.winnerTeam')->findOrFail($tournamentId);

        return response()->json($tournament->baskets);
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
}
