<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Метод для обновления профиля пользователя
    public function updateProfile(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:8192', // Проверка, что аватар — это изображение
            'password' => 'nullable|string|min:8|confirmed', // Обновление пароля (если передан)
        ]);

        // Если валидация не прошла
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Получаем текущего пользователя
        $user = Auth::user();

        // Обновляем только те данные, которые были переданы в запросе
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->hasFile('avatar')) {
            // Проверка наличия файла и его сохранение
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        if ($request->has('password')) {
            // Хешируем новый пароль
            $user->password = Hash::make($request->password);
        }

        // Сохраняем изменения
        $user->save();

        // Возвращаем обновлённые данные пользователя
        return response()->json([
            'id' => $user->id,
            'avatar' => asset('storage/' . $user->avatar), // Возвращаем полный путь к изображению
            'role' => $user->role->name, // Добавляем роль пользователя
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
}
