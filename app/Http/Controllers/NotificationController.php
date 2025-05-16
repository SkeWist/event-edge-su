<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notification\AcceptTeamRegistrationRequest;
use App\Http\Requests\Notification\NotifyMatchRescheduleRequest;
use App\Http\Requests\Notification\NotifyMatchResultRequest;
use App\Http\Requests\Notification\NotifyNextStageRequest;
use App\Http\Requests\Notification\NotifyTeamEliminationRequest;
use App\Http\Requests\Notification\NotifyTournamentRegistrationRequest;
use App\Http\Requests\Notification\SendReminderRequest;
use App\Models\GameMatch;
use App\Models\Notification;
use App\Models\Stage;
use App\Models\Team;
use App\Models\TeamTournamentRequest;
use App\Models\Tournament;
use App\Models\User;
use App\Notifications\MatchResultNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function getUserNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        // Получаем уведомления с дополнительными данными
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($notification) {
                $data = json_decode($notification->data, true);

                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'status' => $notification->status,
                    'created_at' => $notification->created_at,
                    'data' => $data, // Добавляем декодированные данные
                    'type' => $data['type'] ?? null
                ];
            });

        // Считаем количество непрочитанных уведомлений
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }
    public function getUnreadNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        // Получаем все непрочитанные уведомления пользователя
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'message', 'created_at']);

        // Отмечаем их как "прочитанные"
        Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json($unreadNotifications);
    }
    public function notifyTournamentRegistrationOpen($tournamentId): JsonResponse
    {
        // Находим турнир
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Формируем сообщение
        $message = "Регистрация на турнир '{$tournament->name}' открыта!";

        // Получаем всех пользователей
        $users = User::all();

        // Отправляем уведомления
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return response()->json(['message' => 'Уведомления отправлены всем пользователям.']);
    }
    public function notifyRegistrationClosed($tournamentId): JsonResponse
    {
        // Находим турнир
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Получаем организатора турнира
        $organizer = User::find($tournament->user_id);

        if (!$organizer) {
            return response()->json(['error' => 'Организатор турнира не найден'], 404);
        }

        // Формируем сообщение
        $message = "Регистрация на турнир '{$tournament->name}' завершена. Проверьте список участников!";

        // Создаём уведомление только для организатора
        Notification::create([
            'user_id' => $organizer->id,
            'message' => $message,
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Уведомление отправлено организатору турнира.']);
    }
    public function notifyTournamentStart($tournamentId): JsonResponse
    {
        // Находим турнир
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Получаем всех участников турнира через команды
        $participants = DB::table('team_user')
            ->join('tournament_teams', 'team_user.team_id', '=', 'tournament_teams.team_id')
            ->where('tournament_teams.tournament_id', $tournamentId)
            ->pluck('team_user.user_id')
            ->unique();

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'У турнира нет участников'], 400);
        }

        // Формируем сообщение
        $message = "Турнир '{$tournament->name}' стартовал! Проверьте свою первую игру.";

        // Добавляем уведомления в базу для всех участников
        $notifications = $participants->map(function ($userId) use ($message) {
            return [
                'user_id' => $userId,
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        Notification::insert($notifications->toArray());

        return response()->json(['message' => 'Уведомления отправлены всем участникам турнира.']);
    }
    public function notifyMatchReschedule(NotifyMatchRescheduleRequest $request, $matchId): JsonResponse
    {
        // Находим матч
        $match = GameMatch::find($matchId);

        if (!$match) {
            return response()->json(['error' => 'Матч не найден'], 404);
        }

        // Определяем команды в матче
        $team1 = Team::find($match->team_1_id);
        $team2 = Team::find($match->team_2_id);

        if (!$team1 || !$team2) {
            return response()->json(['error' => 'У матча нет корректных команд'], 400);
        }

        // Получаем всех участников обеих команд
        $participants = DB::table('team_user')
            ->whereIn('team_id', [$team1->id, $team2->id])
            ->pluck('user_id')
            ->unique();

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'Нет участников для уведомления'], 400);
        }

        // Формируем уведомления для каждой команды с указанием соперника
        $notifications = collect();

        foreach ($participants as $userId) {
            // Определяем команду пользователя
            $userTeam = DB::table('team_user')->where('user_id', $userId)->value('team_id');

            // Определяем соперника
            $opponentTeam = ($userTeam == $team1->id) ? $team2->name : $team1->name;

            // Формируем сообщение
            $message = "Ваш матч против {$opponentTeam} перенесён на " . $request->new_time . ".";

            $notifications->push([
                'user_id' => $userId,
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Сохраняем уведомления в БД
        Notification::insert($notifications->toArray());

        return response()->json([
            'message' => 'Уведомления отправлены всем участникам команд.',
            'new_time' => $request->new_time,
            'team_1' => $team1->name,
            'team_2' => $team2->name,
        ]);
    }
    public function notifyMatchResult(NotifyMatchResultRequest $request, $matchId): JsonResponse
    {
        // Находим матч
        $match = GameMatch::find($matchId);

        if (!$match) {
            return response()->json(['error' => 'Матч не найден'], 404);
        }

        // Проверяем, что указан корректный номер команды (1 или 2)
        if (!in_array($request->winner_team_id, [1, 2])) {
            return response()->json(['error' => 'Некорректный номер команды-победителя. Используйте 1 для первой команды или 2 для второй'], 400);
        }

        // Получаем команды матча
        $team1 = Team::find($match->team_1_id);
        $team2 = Team::find($match->team_2_id);

        if (!$team1 || !$team2) {
            return response()->json(['error' => 'У матча нет корректных команд'], 400);
        }

        // Определяем победителя и проигравшего по номеру команды
        $winnerTeam = $request->winner_team_id == 1 ? $team1 : $team2;
        $loserTeam = $request->winner_team_id == 1 ? $team2 : $team1;

        // Обновляем данные матча
        $match->update([
            'winner_team_id' => $winnerTeam->id,
            'result' => $request->result,
        ]);

        // Получаем участников обеих команд
        $participants = DB::table('team_user')
            ->whereIn('team_id', [$team1->id, $team2->id])
            ->pluck('user_id')
            ->unique();

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'Нет участников для уведомления'], 400);
        }

        // Создаем уведомления для всех участников
        $notifications = collect();

        foreach ($participants as $userId) {
            // Определяем команду пользователя
            $userTeam = DB::table('team_user')
                ->where('user_id', $userId)
                ->whereIn('team_id', [$team1->id, $team2->id])
                ->value('team_id');

            $isWinner = $userTeam == $winnerTeam->id;

            $message = $isWinner
                ? "Вы победили команду {$loserTeam->name} со счётом {$request->result}!"
                : "Вы проиграли команде {$winnerTeam->name} со счётом {$request->result}.";

            $notifications->push([
                'user_id' => $userId,
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Сохраняем уведомления в БД
        Notification::insert($notifications->toArray());

        return response()->json([
            'message' => 'Результат матча сохранён и уведомления отправлены.',
            'winner' => $winnerTeam->name,
            'loser' => $loserTeam->name,
            'result' => $request->result,
        ]);
    }


    public function notifyNextStage(NotifyNextStageRequest $request, $tournamentId): JsonResponse
    {
        // Находим турнир по ID
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Получаем команды, участвующие в турнире
        $teamsInTournament = DB::table('tournament_teams')
            ->where('tournament_id', $tournamentId)
            ->pluck('team_id');

        // Проверяем, что команда участвует в турнире
        if (!in_array($request->team_id, $teamsInTournament->toArray())) {
            return response()->json(['error' => 'Команда не участвует в данном турнире'], 400);
        }

        // Получаем участников команды
        $participants = DB::table('team_user')
            ->where('team_id', $request->team_id)
            ->pluck('user_id');

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'Нет участников для уведомления'], 400);
        }

        // Получаем название этапа по stage_id
        $stage = Stage::find($request->stage_id);

        if (!$stage) {
            return response()->json(['error' => 'Этап не найден'], 404);
        }

        // Формируем сообщение с названием этапа
        $message = "Поздравляем! Ваша команда вышла в " . $stage->name . " турнира " . $tournament->name . ".";

        // Создаём уведомления для участников
        $notifications = $participants->map(function ($userId) use ($message) {
            return [
                'user_id' => $userId,
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Вставляем уведомления в таблицу
        Notification::insert($notifications->toArray());

        return response()->json(['message' => 'Уведомления отправлены участникам команды.']);
    }
    public function notifyTeamElimination(NotifyTeamEliminationRequest $request, $tournamentId): JsonResponse
    {
        // Находим турнир по ID
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Получаем участников команды
        $participants = DB::table('team_user')
            ->where('team_id', $request->team_id)
            ->pluck('user_id');

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'Нет участников для уведомления'], 400);
        }

        // Формируем сообщение
        $message = "К сожалению, ваша команда покидает турнир " . $tournament->name . ".";

        // Создаём уведомления для участников
        $notifications = $participants->map(function ($userId) use ($message) {
            return [
                'user_id' => $userId,
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Вставляем уведомления в таблицу
        Notification::insert($notifications->toArray());

        return response()->json(['message' => 'Уведомления отправлены участникам команды.']);
    }
  public function notifyTournamentRegistration(NotifyTournamentRegistrationRequest $request): JsonResponse
  {
    $userId = Auth::id();
    $tournament = Tournament::findOrFail($request->tournament_id);

    // Находим команду пользователя с названием
    $teamUser = DB::table('team_user')
      ->join('teams', 'team_user.team_id', '=', 'teams.id')
      ->where('team_user.user_id', $userId)
      ->select('team_user.team_id', 'teams.name as team_name')
      ->first();

    if (!$teamUser) {
      return response()->json(['error' => 'Вы не состоите ни в одной команде'], 400);
    }

    $teamId = $teamUser->team_id;
    $teamName = $teamUser->team_name;

    // Проверяем, не подана ли уже заявка
    if (DB::table('team_tournament_requests')->where('team_id', $teamId)->where('tournament_id', $tournament->id)->exists()) {
      return response()->json(['error' => 'Заявка уже существует'], 422);
    }

    // Проверяем, не участвует ли уже команда в турнире
    if (DB::table('tournament_teams')->where('team_id', $teamId)->where('tournament_id', $tournament->id)->exists()) {
      return response()->json(['error' => 'Команда уже участвует в турнире'], 422);
    }

    DB::beginTransaction();
    try {
      // Создаём заявку
      DB::table('team_tournament_requests')->insert([
        'team_id' => $teamId,
        'tournament_id' => $tournament->id,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now()
      ]);

      // Уведомление организатору с названием команды вместо ID
      Notification::create([
        'user_id' => $tournament->user_id,
        'message' => "Команда '{$teamName}' подала заявку на участие в турнире '{$tournament->name}'",
        'status' => 'unread',
        'data' => json_encode([
          'type' => 'tournament_registration',
          'team_id' => $teamId,
          'team_name' => $teamName, // Добавляем название команды в данные
          'tournament_id' => $tournament->id
        ])
      ]);

      DB::commit();
      return response()->json(['message' => 'Заявка отправлена']);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => 'Ошибка при отправке заявки'], 500);
    }
  }
  public function listTournamentRequests($tournamentId): JsonResponse
  {
    // Проверяем, что турнир существует
    $tournament = Tournament::find($tournamentId);
    if (!$tournament) {
      return response()->json(['error' => 'Турнир не найден'], 404);
    }

    // Получаем заявки на этот турнир
    $requests = DB::table('team_tournament_requests as ttr')
      ->join('teams as t', 't.id', '=', 'ttr.team_id')
      ->where('ttr.tournament_id', $tournamentId)
      ->select([
        'ttr.id',
        'ttr.team_id',
        't.name as team_name',
        'ttr.status',
        'ttr.created_at',
        'ttr.updated_at'
      ])
      ->orderByDesc('ttr.created_at')
      ->get();

    return response()->json(['requests' => $requests]);
  }
    public function acceptTeamRegistration(int $requestId): JsonResponse
    {
        $request = DB::table('team_tournament_requests')->where('id', $requestId)->first();
        if (!$request) {
            return response()->json(['error' => 'Заявка не найдена'], 404);
        }
        $tournament = Tournament::findOrFail($request->tournament_id);
        if ($tournament->user_id !== Auth::id()) {
            return response()->json(['error' => 'Нет прав'], 403);
        }
        if ($request->status !== 'pending') {
            return response()->json(['error' => 'Заявка уже обработана'], 400);
        }

        DB::beginTransaction();
        try {
            // Добавляем команду в турнир
            DB::table('tournament_teams')->insert([
                'tournament_id' => $request->tournament_id,
                'team_id' => $request->team_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // Обновляем статус заявки
            DB::table('team_tournament_requests')->where('id', $requestId)->update(['status' => 'accepted', 'updated_at' => now()]);
            // Уведомляем всех участников команды
            $teamMembers = DB::table('team_user')->where('team_id', $request->team_id)->pluck('user_id');
            foreach ($teamMembers as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'message' => "Ваша команда принята в турнир {$tournament->name}",
                    'status' => 'unread',
                    'data' => json_encode([
                        'type' => 'tournament_registration_response',
                        'status' => 'accepted',
                        'tournament_id' => $request->tournament_id,
                        'team_id' => $request->team_id
                    ])
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Заявка принята']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при принятии заявки'], 500);
        }
    }
    public function rejectTeamRegistration(int $requestId): JsonResponse
    {
        $request = DB::table('team_tournament_requests')->where('id', $requestId)->first();
        if (!$request) {
            return response()->json(['error' => 'Заявка не найдена'], 404);
        }
        $tournament = Tournament::findOrFail($request->tournament_id);
        if ($tournament->user_id !== Auth::id()) {
            return response()->json(['error' => 'Нет прав'], 403);
        }
        if ($request->status !== 'pending') {
            return response()->json(['error' => 'Заявка уже обработана'], 400);
        }

        DB::beginTransaction();
        try {
            // Обновляем статус заявки
            DB::table('team_tournament_requests')->where('id', $requestId)->update(['status' => 'rejected', 'updated_at' => now()]);
            // Уведомляем всех участников команды
            $teamMembers = DB::table('team_user')->where('team_id', $request->team_id)->pluck('user_id');
            foreach ($teamMembers as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'message' => "Ваша команда не принята в турнир {$tournament->name}",
                    'status' => 'unread',
                    'data' => json_encode([
                        'type' => 'tournament_registration_response',
                        'status' => 'rejected',
                        'tournament_id' => $request->tournament_id,
                        'team_id' => $request->team_id
                    ])
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Заявка отклонена']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при отклонении заявки'], 500);
        }
    }
    public function sendRemainderTeam(SendReminderRequest $request, $matchId): JsonResponse
    {
        // Находим матч по ID
        $match = GameMatch::find($matchId);

        if (!$match) {
            return response()->json(['error' => 'Матч не найден'], 404);
        }

        // Проверяем, что матч ещё не начался
        if ($match->match_date <= now()) {
            return response()->json(['error' => 'Матч уже начался'], 400);
        }

        // Вычисляем разницу во времени
        $timeDifference = $match->match_date->diffInMinutes(now());

        $reminderTimeInMinutes = $request->unit == 'hours' ? $request->reminder_time * 60 : $request->reminder_time;

        // Проверяем, что напоминание не сработает, если матч уже начнется в заданный промежуток времени
        if ($timeDifference <= $reminderTimeInMinutes) {
            return response()->json(['error' => 'Невозможно отправить напоминание, матч начнется в этот промежуток времени.'], 400);
        }

        // Получаем команды, участвующие в матче
        $teams = [$match->team_1_id, $match->team_2_id];

        // Получаем всех участников этих команд
        $participants = DB::table('team_user')
            ->whereIn('team_id', $teams)
            ->pluck('user_id')
            ->unique();

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'Нет участников для уведомления'], 400);
        }

        // Формируем сообщение
        $team1 = Team::find($match->team_1_id)->name;
        $team2 = Team::find($match->team_2_id)->name;
        $message = "Ваш матч против $team2 начнётся через $request->reminder_time $request->unit.";

        // Отправляем уведомления для участников
        $notifications = $participants->map(function ($userId) use ($message) {
            return [
                'user_id' => $userId,
                'message' => $message,
                'status' => 'unread',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // Вставляем уведомления в таблицу notifications
        Notification::insert($notifications->toArray());

        return response()->json(['message' => 'Напоминания отправлены участникам матча.']);
    }
}
