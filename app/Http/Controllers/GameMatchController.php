<?php

namespace App\Http\Controllers;

use App\Http\Requests\GameMatch\StoreGameMatchRequest;
use App\Http\Requests\GameMatch\UpdateGameMatchRequest;
use App\Models\GameMatch;
use App\Models\TournamentBasket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class GameMatchController extends Controller
{
    /**
     * Просмотр всех матчей.
     */
    public function index(): JsonResponse
    {
        $matches = GameMatch::with(['game', 'teamA', 'teamB', 'stage'])->get()
            ->makeHidden(['game_id', 'team_1_id', 'team_2_id', 'created_at', 'updated_at']);

        $matches->each(function ($match) {
            $match->game_name = $match->game->name ?? 'Неизвестная игра';
            $match->team_1_name = $match->teamA->name ?? 'Неизвестная команда';
            $match->team_2_name = $match->teamB->name ?? 'Неизвестная команда';
            $match->stage_name = $match->stage->name ?? 'Этап не указан';

            unset($match->game, $match->teamA, $match->teamB, $match->stage);
        });

        return response()->json($matches);
    }

    /**
     * Просмотр матча по id.
     */
    public function show(int $id): JsonResponse
    {
        $match = GameMatch::with(['game', 'teamA', 'teamB', 'stage'])->findOrFail($id)
            ->makeHidden(['game_id', 'team_1_id', 'team_2_id', 'created_at', 'updated_at']);

        $match->game_name = $match->game->name ?? 'Неизвестная игра';
        $match->team_1_name = $match->teamA->name ?? 'Неизвестная команда';
        $match->team_2_name = $match->teamB->name ?? 'Неизвестная команда';
        $match->stage_name = $match->stage->name ?? 'Этап не указан';

        unset($match->game, $match->teamA, $match->teamB, $match->stage);

        return response()->json($match);
    }

    /**
     * Создание нового матча.
     */
    public function store(StoreGameMatchRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $match_date = Carbon::createFromFormat('Y-m-d\TH:i', $validated['match_date'])
                ->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Неверный формат даты и времени'], 400);
        }

        $validated['match_date'] = $match_date;
        $match = GameMatch::create($validated);

        return response()->json([
            'message' => 'Матч успешно создан!',
            'match' => $match->makeHidden('id'),
        ], 201);
    }

    /**
     * Редактирование существующего матча.
     */
    public function update(UpdateGameMatchRequest $request, int $id): JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $validated = $request->validated();

        if (isset($validated['match_date'])) {
            try {
                $validated['match_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $validated['match_date'])
                    ->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Неверный формат даты и времени'], 400);
            }
        }

        // Если статус меняется на completed, проверяем наличие результата и победителя
        if ($validated['status'] === 'completed' && (!isset($validated['result']) || !isset($validated['winner_team_id']))) {
            return response()->json([
                'error' => 'При завершении матча необходимо указать результат и команду-победителя'
            ], 400);
        }

        $match->update(array_filter($validated, fn($value) => $value !== null));

        return response()->json([
            'message' => 'Матч успешно обновлён!',
            'match' => $match->makeHidden('id'),
        ]);
    }

    /**
     * Удаление матча.
     */
    public function destroy(int $id): JsonResponse
    {
        $match = GameMatch::findOrFail($id);
        $match->delete();

        return response()->json([
            'message' => 'Матч успешно удалён!',
        ]);
    }

    /**
     * Получение матчей пользователя.
     */
    public function myMatches(): JsonResponse
    {
        $user = auth()->user();
        $now = Carbon::now();

        $teamIds = $user->teams()->pluck('teams.id');

        if ($teamIds->isEmpty()) {
            return response()->json(['error' => 'У вас нет привязанных команд.'], 404);
        }

        $matchesQuery = GameMatch::with(['game', 'teamA', 'teamB', 'stage', 'winnerTeam'])
            ->where(function ($query) use ($teamIds) {
                $query->whereIn('team_1_id', $teamIds)
                    ->orWhereIn('team_2_id', $teamIds);
            });

        $pastMatches = (clone $matchesQuery)
            ->where('match_date', '<', $now)
            ->orderBy('match_date', 'desc')
            ->get();

        $upcomingMatches = (clone $matchesQuery)
            ->where('match_date', '>=', $now)
            ->orderBy('match_date', 'asc')
            ->get();

        $formatMatches = function ($matches) {
            return $matches->map(function ($match) {
                $match->game_name = $match->game->name ?? 'Неизвестная игра';
                $match->team_1_name = $match->teamA->name ?? 'Неизвестная команда';
                $match->team_2_name = $match->teamB->name ?? 'Неизвестная команда';
                $match->stage_name = $match->stage->name ?? 'Этап не указан';
                $match->winner_team_name = $match->winnerTeam->name ?? null;
                
                return $match->makeHidden([
                    'game_id', 'team_1_id', 'team_2_id', 'winner_team_id',
                    'created_at', 'updated_at',
                    'game', 'teamA', 'teamB', 'stage', 'winnerTeam'
                ]);
            });
        };

        return response()->json([
            'past_matches' => $formatMatches($pastMatches),
            'upcoming_matches' => $formatMatches($upcomingMatches),
        ]);
    }
}
