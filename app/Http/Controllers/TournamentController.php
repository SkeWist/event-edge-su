<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\User;
use App\Models\Game;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{
    // Просмотр турнира
    //1
    public function index()
    {
        $tournaments = Tournament::all();
        return response()->json($tournaments);
    }
    public function show($id)
    {
        $tournament = Tournament::findOrFail($id);
        return response()->json($tournament);
    }

    // Создание турнира
    public function store(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'user_id' => 'required|exists:users,id',
            'game_id' => 'required|exists:games,id',
            'stage_id' => 'required|exists:stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Создание турнира
        $tournament = Tournament::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'user_id' => $request->user_id,
            'game_id' => $request->game_id,
            'stage_id' => $request->stage_id,
        ]);

        return response()->json([
            'message' => 'Турнир успешно создан!',
            'tournament' => $tournament
        ], 201);
    }

    // Редактирование турнира
    public function update(Request $request, $id)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'user_id' => 'nullable|exists:users,id',
            'game_id' => 'nullable|exists:games,id',
            'stage_id' => 'nullable|exists:stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Находим турнир по ID
        $tournament = Tournament::findOrFail($id);

        // Обновляем данные турнира
        $tournament->update($request->only([
            'name',
            'description',
            'start_date',
            'end_date',
            'user_id',
            'game_id',
            'stage_id'
        ]));

        return response()->json([
            'message' => 'Турнир успешно обновлен!',
            'tournament' => $tournament
        ]);
    }

    // Удаление турнира
    public function destroy($id)
    {
        // Находим турнир по ID
        $tournament = Tournament::findOrFail($id);

        // Удаляем турнир
        $tournament->delete();

        return response()->json([
            'message' => 'Турнир успешно удален!'
        ]);
    }
}
