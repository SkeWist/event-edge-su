<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Получение списка всех команд
     */
    public function index(): JsonResponse
    {
        $teams = Team::all();

        return response()->json([
            'message' => 'Список команд успешно получен',
            'data' => $teams
        ]);
    }

    /**
     * Получение информации о команде
     */
    public function show(int $id): JsonResponse
    {
        $team = Team::findOrFail($id);

        return response()->json([
            'message' => 'Информация о команде успешно получена',
            'data' => $team
        ]);
    }
  public function tournaments($teamId)
  {
    $team = Team::with(['tournaments' => function ($query) {
      $query->select('tournaments.id', 'tournaments.name', 'tournaments.start_date');
    }])->findOrFail($teamId);

    // Можно дополнительно упростить:
    $tournaments = $team->tournaments->map(function ($tournament) {
      return [
        'id' => $tournament->id,
        'name' => $tournament->name,
        'date' => $tournament->start_date,
      ];
    });

    return response()->json(['data' => $tournaments]);
  }
    /**
     * Получение списка участников команды
     */
    public function getTeamMembers(int $teamId): JsonResponse
    {
        $team = Team::with('users')->findOrFail($teamId);

        return response()->json([
            'message' => 'Список участников команды успешно получен',
            'data' => [
                'team' => $team->name,
                'members' => $team->users->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
            ]
        ]);
    }

    /**
     * Получение списка команд пользователя
     */
    public function getUserTeams(int $userId): JsonResponse
    {
        $user = User::with('teams')->findOrFail($userId);

        return response()->json([
            'message' => 'Список команд пользователя успешно получен',
            'data' => [
                'user' => $user->name,
                'teams' => $user->teams->map(fn($team) => [
                    'id' => $team->id,
                    'name' => $team->name,
                ]),
            ]
        ]);
    }

    /**
     * Создание новой команды
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = Team::create($request->validated());

        return response()->json([
            'message' => 'Команда успешно создана!',
            'data' => $team
        ], 201);
    }

    /**
     * Обновление информации о команде
     */
    public function update(UpdateTeamRequest $request, int $id): JsonResponse
    {
        $team = Team::findOrFail($id);
        $validatedData = $request->validated();

        // Проверяем, есть ли реальные изменения в данных
        $hasChanges = false;
        foreach ($validatedData as $field => $value) {
            if ($team->$field !== $value) {
                $hasChanges = true;
                break;
            }
        }

        if (!$hasChanges) {
            return response()->json([
                'error' => 'Нет изменений для сохранения'
            ], 422);
        }

        $team->update($validatedData);

        return response()->json([
            'message' => 'Команда успешно обновлена!',
            'data' => $team
        ]);
    }

    /**
     * Выход из команды
     */
    public function leaveTeam(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден.'], 401);
        }

        $currentTeam = $user->teams()->wherePivot('status', 'active')->first();

        if (!$currentTeam) {
            return response()->json(['error' => 'Вы не состоите в команде.'], 400);
        }

        DB::table('team_user')
            ->where('user_id', $user->id)
            ->where('team_id', $currentTeam->id)
            ->update(['status' => 'left']);

        return response()->json([
            'message' => 'Вы успешно вышли из команды.'
        ]);
    }

    /**
     * Удаление команды
     */
    public function destroy(int $id): JsonResponse
    {
        $team = Team::findOrFail($id);
        $team->delete();

        return response()->json([
            'message' => 'Команда успешно удалена!'
        ], 204);
    }
}
