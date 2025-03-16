<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
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

        // Получаем участие пользователя в турнире
        $participant = Participant::where('user_id', $userId)->first();

        if (!$participant || !$participant->team) {
            return response()->json([
                'error' => 'У пользователя нет команды.',
            ], 404);
        }

        // Получаем команду пользователя
        $team = $participant->team;

        // Получаем турниры, в которых участвовала команда
        $tournaments = Tournament::whereHas('teams', function ($query) use ($team) {
            $query->where('teams.id', $team->id);
        })->get();

        // Получаем текущий турнир, в котором участвует команда
        $currentTournament = Tournament::whereHas('teams', function ($query) use ($team) {
            $query->where('teams.id', $team->id);
        })->whereNull('end_date')->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'team' => $team ? $team->name : null,
                'token' => $user->api_token, // Добавляем токен пользователя
            ],
            'tournaments' => $tournaments->map(function ($tournament) {
                return [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'start_date' => $tournament->start_date,
                    'end_date' => $tournament->end_date,
                ];
            }),
            'current_tournament' => $currentTournament ? [
                'id' => $currentTournament->id,
                'name' => $currentTournament->name,
                'start_date' => $currentTournament->start_date,
                'end_date' => $currentTournament->end_date,
            ] : null,
        ]);
    }
    public function myProfile(Request $request)
    {
        // Принудительно загружаем JSON-данные в запрос
        $request->merge(json_decode($request->getContent(), true) ?? []);

        // Получаем токен из тела запроса
        $token = $request->input('token');

        if (!$token) {
            return response()->json(['error' => 'Токен обязателен.'], 400);
        }

        // Убираем возможный префикс "Bearer "
        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        // Находим пользователя по токену
        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден или токен недействителен.'], 401);
        }

        // Получаем участие пользователя в турнире
        $participant = Participant::where('user_id', $user->id)->first();

        if (!$participant || !$participant->team) {
            return response()->json(['error' => 'У пользователя нет команды.'], 404);
        }

        // Получаем команду пользователя
        $team = $participant->team;

        // Получаем турниры, в которых участвовала команда
        $tournaments = Tournament::whereHas('teams', function ($query) use ($team) {
            $query->where('teams.id', $team->id);
        })->get();

        // Получаем текущий турнир, в котором участвует команда
        $currentTournament = Tournament::whereHas('teams', function ($query) use ($team) {
            $query->where('teams.id', $team->id);
        })->whereNull('end_date')->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'team' => $team->name,
                'token' => $user->api_token, // Возвращаем токен пользователя
            ],
            'tournaments' => $tournaments->map(function ($tournament) {
                return [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'start_date' => $tournament->start_date,
                    'end_date' => $tournament->end_date,
                ];
            }),
            'current_tournament' => $currentTournament ? [
                'id' => $currentTournament->id,
                'name' => $currentTournament->name,
                'start_date' => $currentTournament->start_date,
                'end_date' => $currentTournament->end_date,
            ] : null,
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
