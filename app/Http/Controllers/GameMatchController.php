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
        $matches = GameMatch::all();
        return response()->json($matches);
    }

    /**
     * Просмотр конкретного матча.
     */
    public function show($id)
    {
        $match = GameMatch::findOrFail($id);
        return response()->json($match);
    }

    /**
     * Создание нового матча.
     */
    public function store(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|exists:games,id',
            'team_1_id' => 'required|exists:teams,id',
            'team_2_id' => 'required|exists:teams,id',
            'match_date' => 'required|date_format:d.m.Y', // Ожидаем формат DD.MM.YYYY
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
            'match_date' => $request->match_date,
            'status' => $request->status,
            'winner_team_id' => $request->winner_team_id,
            'stage_id' => $request->stage_id,
            'result' => $request->result,
        ]);

        return response()->json([
            'message' => 'Матч успешно создан!',
            'match' => $match,
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
            'match_date' => 'nullable|date',
            'status' => 'nullable|in:scheduled,in_progress,completed',
            'winner_team_id' => 'nullable|exists:teams,id',
            'stage_id' => 'nullable|exists:stages,id',
            'result' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Находим матч по ID
        $match = GameMatch::findOrFail($id);

        // Обновление данных матча
        $match->update($request->only([
            'game_id', 'team_1_id', 'team_2_id', 'match_date', 'status',
            'winner_team_id', 'stage_id', 'result'
        ]));

        return response()->json([
            'message' => 'Матч успешно обновлён!',
            'match' => $match,
        ]);
    }

    /**
     * Удаление матча.
     */
    public function destroy($id)
    {
        // Находим матч по ID
        $match = GameMatch::findOrFail($id);

        // Удаляем матч
        $match->delete();

        return response()->json([
            'message' => 'Матч успешно удалён!',
        ]);
    }
}
