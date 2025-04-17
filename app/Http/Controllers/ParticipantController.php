<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ParticipantController extends Controller
{
    // Создание участника турнира
    public function store(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id', // Проверка на существование пользователя
            'team_id' => 'nullable|exists:teams,id', // Проверка на существование команды
            'tournament_id' => 'required|exists:tournaments,id', // Проверка на существование турнира
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Создание участника
        $participant = Participant::create([
            'user_id' => $request->user_id,
            'team_id' => $request->team_id,
            'tournament_id' => $request->tournament_id,
        ]);

        return response()->json([
            'message' => 'Участник успешно добавлен!',
            'participant' => $participant
        ], 201);
    }

    // Редактирование участника турнира
    public function update(Request $request, $id)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id', // Проверка на существование пользователя
            'team_id' => 'nullable|exists:teams,id', // Проверка на существование команды
            'tournament_id' => 'nullable|exists:tournaments,id', // Проверка на существование турнира
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Находим участника по ID
        $participant = Participant::findOrFail($id);

        // Обновляем участника
        $participant->update($request->only(['user_id', 'team_id', 'tournament_id']));

        return response()->json([
            'message' => 'Участник успешно обновлен!',
            'participant' => $participant
        ]);
    }
    public function profile($userId)
    {
        // Находим пользователя
        $user = User::findOrFail($userId);

        // Получаем текущую команду пользователя
        $currentTeam = $user->teams()->wherePivot('status', 'active')->first();

        // Получаем список участников текущей команды
        $teamMembers = $currentTeam ? DB::table('users')
            ->select('users.id', 'users.name', 'users.email')
            ->join('team_user', 'users.id', '=', 'team_user.user_id')
            ->where('team_user.team_id', $currentTeam->id)
            ->get() : collect();

        // Получаем прошлые команды, в которых был пользователь
        $pastTeams = DB::table('teams')
            ->select('teams.id', 'teams.name')
            ->join('team_user', 'teams.id', '=', 'team_user.team_id')
            ->where('team_user.user_id', $user->id)
            ->where('team_user.status', 'left')
            ->get();

        // Получаем турниры, в которых участвовала текущая команда
        $tournaments = $currentTeam ? DB::table('tournaments')
            ->select('tournaments.id', 'tournaments.name', 'tournaments.start_date', 'tournaments.end_date')
            ->join('tournament_teams', 'tournaments.id', '=', 'tournament_teams.tournament_id')
            ->where('tournament_teams.team_id', $currentTeam->id)
            ->get() : collect();

        // Получаем текущий турнир, в котором участвует команда
        $currentTournament = $currentTeam ? DB::table('tournaments')
            ->select('tournaments.id', 'tournaments.name', 'tournaments.start_date', 'tournaments.end_date')
            ->join('tournament_teams', 'tournaments.id', '=', 'tournament_teams.tournament_id')
            ->where('tournament_teams.team_id', $currentTeam->id)
            ->whereNull('tournaments.end_date')
            ->first() : null;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'team' => $currentTeam ? [
                    'id' => $currentTeam->id,
                    'name' => $currentTeam->name,
                    'members' => $teamMembers,
                ] : 'Команда не присоединена',
            ],
            'past_teams' => $pastTeams,
            'tournaments' => $tournaments,
            'current_tournament' => $currentTournament,
        ]);
    }
    public function myProfile(Request $request)
    {
        // Получаем текущего авторизированного пользователя
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден или токен недействителен.'], 401);
        }

        // Получаем текущую команду пользователя
        $currentTeam = $user->teams()->wherePivot('status', 'active')->first();

        // Получаем список участников текущей команды
        $teamMembers = $currentTeam ? DB::table('users')
            ->select('users.id', 'users.name', 'users.email')
            ->join('team_user', 'users.id', '=', 'team_user.user_id')
            ->where('team_user.team_id', $currentTeam->id)
            ->get() : collect();

        // Получаем прошлые команды, в которых был пользователь
        $pastTeams = DB::table('teams')
            ->select('teams.id', 'teams.name')
            ->join('team_user', 'teams.id', '=', 'team_user.team_id')
            ->where('team_user.user_id', $user->id)
            ->where('team_user.status', 'left')
            ->get();

        // Получаем турниры, в которых участвовала текущая команда
        $tournaments = $currentTeam ? DB::table('tournaments')
            ->select('tournaments.id', 'tournaments.name', 'tournaments.start_date', 'tournaments.end_date')
            ->join('tournament_teams', 'tournaments.id', '=', 'tournament_teams.tournament_id')
            ->where('tournament_teams.team_id', $currentTeam->id)
            ->get() : collect();

        // Получаем текущий турнир, в котором участвует команда
        $currentTournament = $currentTeam ? DB::table('tournaments')
            ->select('tournaments.id', 'tournaments.name', 'tournaments.start_date', 'tournaments.end_date')
            ->join('tournament_teams', 'tournaments.id', '=', 'tournament_teams.tournament_id')
            ->where('tournament_teams.team_id', $currentTeam->id)
            ->whereNull('tournaments.end_date')
            ->first() : null;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar'=>$user->avatar,
                'team' => $currentTeam ? [
                    'id' => $currentTeam->id,
                    'name' => $currentTeam->name,
                    'members' => $teamMembers,
                ] : 'Команда не присоединена',
            ],
            'past_teams' => $pastTeams,
            'tournaments' => $tournaments,
            'current_tournament' => $currentTournament,
        ]);
    }
    // Удаление участника турнира
    public function destroy($id)
    {
        // Находим участника по ID
        $participant = Participant::findOrFail($id);

        // Удаляем участника
        $participant->delete();

        return response()->json([
            'message' => 'Участник успешно удален!'
        ]);
    }
}
