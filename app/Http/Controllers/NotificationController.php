<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Notification;
use App\Models\Stage;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function getUserNotifications(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        // Получаем последние 50 уведомлений пользователя (с учетом статуса)
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get(['id', 'message', 'status', 'created_at']);

        // Считаем количество непрочитанных уведомлений
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount, // Добавлен счетчик
            'notifications' => $notifications
        ]);
    }
    public function getUnreadNotifications(Request $request)
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
    public function notifyTournamentRegistrationOpen($tournamentId)
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
    public function notifyRegistrationClosed($tournamentId)
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
    public function notifyTournamentStart($tournamentId)
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
    public function notifyMatchReschedule(Request $request, $matchId)
    {
        // Валидация данных
        $request->validate([
            'new_time' => 'required|date_format:H:i', // Новый формат времени HH:MM
        ]);

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
    public function notifyMatchResult(Request $request, $matchId)
    {
        // Валидация данных
        $request->validate([
            'winner_team_id' => 'required|exists:teams,id', // ID команды-победителя
            'result' => 'required|string', // Счёт матча (например, "2:1")
        ]);

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

        // Определяем победителя и проигравшего
        $winnerTeam = ($match->team_1_id == $request->team_winner_id) ? $team1 : $team2;
        $loserTeam = ($winnerTeam->id == $team1->id) ? $team2 : $team1;

        // Получаем всех участников обеих команд
        $participants = DB::table('team_user')
            ->whereIn('team_id', [$team1->id, $team2->id])
            ->pluck('user_id')
            ->unique();

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'Нет участников для уведомления'], 400);
        }

        // Формируем уведомления
        $notifications = collect();

        foreach ($participants as $userId) {
            // Определяем, в какой команде находится пользователь
            $userTeamId = DB::table('team_user')->where('user_id', $userId)->value('team_id');

            // Определяем сообщение в зависимости от команды пользователя
            if ($userTeamId == $winnerTeam->id) {
                $message = "Вы победили команду {$loserTeam->name} со счётом {$request->result}!";
            } else {
                $message = "Вы проиграли команде {$winnerTeam->name} со счётом {$request->result}.";
            }

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
            'message' => 'Уведомления о результате матча отправлены.',
            'winner' => $winnerTeam->name,
            'loser' => $loserTeam->name,
            'result' => $request->result,
        ]);
    }
    public function notifyNextStage(Request $request, $tournamentId)
    {
        // Валидация данных
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id', // Проверка на существование команды
            'stage_id' => 'required|exists:stages,id', // Проверка на существование этапа
        ]);

        $teamId = $validated['team_id'];
        $stageId = $validated['stage_id'];

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
        if (!in_array($teamId, $teamsInTournament->toArray())) {
            return response()->json(['error' => 'Команда не участвует в данном турнире'], 400);
        }

        // Получаем участников команды
        $participants = DB::table('team_user')
            ->where('team_id', $teamId)
            ->pluck('user_id');

        if ($participants->isEmpty()) {
            return response()->json(['error' => 'Нет участников для уведомления'], 400);
        }

        // Получаем название этапа по stage_id
        $stage = Stage::find($stageId);

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
    public function notifyTeamElimination(Request $request, $tournamentId)
    {
        // Валидация данных
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id', // Проверка на существование команды
        ]);

        $teamId = $validated['team_id'];

        // Находим турнир по ID
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Получаем участников команды
        $participants = DB::table('team_user')
            ->where('team_id', $teamId)
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
    public function notifyTournamentRegistration(Request $request, $tournamentId)
    {
        // Валидация данных
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id', // Проверка на существование команды
        ]);

        $teamId = $validated['team_id'];

        // Находим турнир по ID
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Получаем организатора турнира (предполагаем, что организатор — это создатель турнира)
        $organizerId = $tournament->user_id;

        // Получаем информацию о команде
        $team = Team::find($teamId);

        if (!$team) {
            return response()->json(['error' => 'Команда не найдена'], 404);
        }

        // Формируем сообщение
        $message = "Команда " . $team->name . " подала заявку на участие в вашем турнире.";

        // Создаём уведомление для организатора
        $notification = [
            'user_id' => $organizerId,
            'message' => $message,
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Вставляем уведомление в таблицу
        Notification::create($notification);

        return response()->json(['message' => 'Уведомление отправлено организатору турнира.']);
    }
    public function acceptTeamRegistration(Request $request, $tournamentId)
    {
        // Валидация данных
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id', // Проверка на существование команды
        ]);

        $teamId = $validated['team_id'];

        // Находим турнир по ID
        $tournament = Tournament::find($tournamentId);

        if (!$tournament) {
            return response()->json(['error' => 'Турнир не найден'], 404);
        }

        // Проверяем, была ли заявка от этой команды на участие в турнире
        $existingRegistration = DB::table('tournament_teams')
            ->where('tournament_id', $tournamentId)
            ->where('team_id', $teamId)
            ->first();

        if ($existingRegistration) {
            return response()->json(['error' => 'Команда уже зарегистрирована в этом турнире.'], 400);
        }

        // Добавляем команду в таблицу tournament_teams
        DB::table('tournament_teams')->insert([
            'tournament_id' => $tournamentId,
            'team_id' => $teamId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Формируем уведомление для команды, что она принята в турнир
        $team = Team::find($teamId);
        $message = "Ваша команда " . $team->name . " была принята в турнир '" . $tournament->name . "'.";

        // Получаем участников команды
        $participants = DB::table('team_user')
            ->where('team_id', $teamId)
            ->pluck('user_id');

        // Создаём уведомления для участников команды
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

        return response()->json(['message' => 'Заявка на участие принята. Команда добавлена в турнир.']);
    }
    public function sendRemainderTeam(Request $request, $matchId)
    {
        // Валидация данных
        $request->validate([
            'reminder_time' => 'required|integer|min:1', // Время за сколько отправлять напоминание, например 60 (для минут) или 1 (для часов)
            'unit' => 'required|in:minutes,hours', // Указываем, в каких единицах (минуты или часы) мы отправляем напоминание
        ]);

        $reminderTime = $request->reminder_time;
        $unit = $request->unit; // 'minutes' или 'hours'

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

        if ($unit == 'hours') {
            // Если уведомление в часах, преобразуем время в часы
            $reminderTimeInMinutes = $reminderTime * 60;
        } else {
            // Если уведомление в минутах, просто используем это значение
            $reminderTimeInMinutes = $reminderTime;
        }

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
        $message = "Ваш матч против $team2 начнётся через $reminderTime $unit.";

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
