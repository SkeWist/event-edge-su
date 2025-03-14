<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class GameMatchController extends Controller
{
    /**
     * Просмотр всех матчей.
     */
    public function index()
    {
        $matches = GameMatch::with(['game', 'team1', 'team2', 'winnerTeam', 'stage'])->get()
            ->makeHidden(['game_id', 'team_1_id', 'team_2_id', 'winner_team_id', 'stage_id', 'created_at', 'updated_at']);

        $matches->each(function ($match) {
            $match->game_name = $match->game->name ?? 'Неизвестная игра';
            $match->team_1_name = $match->team1->name ?? 'Неизвестная команда';
            $match->team_2_name = $match->team2->name ?? 'Неизвестная команда';
            $match->winner_team_name = $match->winnerTeam->name ?? 'Победитель не определён';
            $match->stage_name = $match->stage->name ?? 'Этап не указан';

            unset($match->game, $match->team1, $match->team2, $match->winnerTeam, $match->stage);
        });

        return response()->json($matches);
    }
    public function show($id)
    {
        $match = GameMatch::with(['game', 'team1', 'team2', 'winnerTeam', 'stage'])->findOrFail($id)
            ->makeHidden(['game_id', 'team_1_id', 'team_2_id', 'winner_team_id', 'stage_id', 'created_at', 'updated_at']);

        $match->game_name = $match->game->name ?? 'Неизвестная игра';
        $match->team_1_name = $match->team1->name ?? 'Неизвестная команда';
        $match->team_2_name = $match->team2->name ?? 'Неизвестная команда';
        $match->winner_team_name = $match->winnerTeam->name ?? 'Победитель не определён';
        $match->stage_name = $match->stage->name ?? 'Этап не указан';

        unset($match->game, $match->team1, $match->team2, $match->winnerTeam, $match->stage);

        return response()->json($match);
    }
    public function store(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|exists:games,id',
            'team_1_id' => 'required|exists:teams,id',
            'team_2_id' => 'required|exists:teams,id',
            'match_date' => 'required|date_format:d.m.Y',
            'status' => 'required|in:scheduled,in_progress,completed',
            'winner_team_id' => 'nullable|exists:teams,id',
            'stage_id' => 'nullable|exists:stages,id',
            'result' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $match_date = Carbon::createFromFormat('d.m.Y', $request->match_date)->format('Y-m-d H:i:s');

        // Создание нового матча
        $match = GameMatch::create([
            'game_id' => $request->game_id,
            'team_1_id' => $request->team_1_id,
            'team_2_id' => $request->team_2_id,
            'match_date' => $match_date,
            'status' => $request->status,
            'winner_team_id' => $request->winner_team_id,
            'stage_id' => $request->stage_id,
            'result' => $request->result,
        ]);

        return response()->json([
            'message' => 'Матч успешно создан!',
            'match' => $match->makeHidden('id'),
        ], 201);
    }

    /**
     * Редактирование существующего матча.
     */
    public function update(Request $request, $id)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'game_id' => 'nullable|exists:games,id',
            'team_1_id' => 'nullable|exists:teams,id',
            'team_2_id' => 'nullable|exists:teams,id',
            'match_date' => 'nullable|date_format:d.m.Y',
            'status' => 'nullable|in:scheduled,in_progress,completed',
            'winner_team_id' => 'nullable|exists:teams,id',
            'stage_id' => 'nullable|exists:stages,id',
            'result' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $match = GameMatch::findOrFail($id);

        $updateData = $request->only([
            'game_id', 'team_1_id', 'team_2_id', 'match_date', 'status',
            'winner_team_id', 'stage_id', 'result'
        ]);

        if ($request->has('match_date')) {
            $updateData['match_date'] = Carbon::createFromFormat('d.m.Y', $request->match_date)->format('Y-m-d H:i:s');
        }

        $match->update(array_filter($updateData));

        return response()->json([
            'message' => 'Матч успешно обновлён!',
            'match' => $match->makeHidden('id'),
        ]);
    }

    /**
     * Удаление матча.
     */
    public function destroy($id)
    {
        $match = GameMatch::findOrFail($id);
        $match->delete();

        return response()->json([
            'message' => 'Матч успешно удалён!',
        ]);
    }
}
