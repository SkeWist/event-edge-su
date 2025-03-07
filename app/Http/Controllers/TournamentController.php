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
        $tournaments = Tournament::with('user')->get()
            ->makeHidden(['id', 'user_id', 'game_id', 'stage_id', 'created_at', 'updated_at']);

        $tournaments->each(function ($tournament) {
            $tournament->user_name = $tournament->user->name ?? 'Неизвестный пользователь';
            unset($tournament->user);
        });

        return response()->json($tournaments);
    }
    public function show($id)
    {
        // Находим турнир
        $tournament = Tournament::with('user')->findOrFail($id)
            ->makeHidden(['id', 'user_id', 'game_id', 'stage_id', 'created_at', 'updated_at']);

        // Обновляем количество просмотров
        $tournament->increment('views_count');

        // Присваиваем имя организатора
        $tournament->user_name = $tournament->user->name ?? 'Неизвестный пользователь';
        unset($tournament->user);

        // Возвращаем турнир с увеличенным счётчиком просмотров
        return response()->json($tournament);
    }
    // Создание турнира
    public function store(Request $request)
    {
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

        $tournament = Tournament::create($request->all());

        return response()->json([
            'message' => "Турнир '{$tournament->name}' успешно создан!",
            'tournament' => $tournament
        ], 201);
    }
    public function popularTournaments()
    {
        // Сортировка по количеству просмотров и получение топ-10 популярных турниров
        $tournaments = Tournament::orderByDesc('views_count')
            ->take(3)
            ->get()
            ->makeHidden(['id', 'user_id', 'game_id', 'stage_id', 'created_at', 'updated_at']);

        $tournaments->each(function ($tournament) {
            $tournament->user_name = $tournament->user->name ?? 'Неизвестный пользователь';
            unset($tournament->user);
        });

        return response()->json($tournaments);
    }
    public function update(Request $request, $id)
    {
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

        $tournament = Tournament::findOrFail($id);
        $tournament->update($request->only([
            'name', 'description', 'start_date', 'end_date', 'user_id', 'game_id', 'stage_id'
        ]));

        return response()->json([
            'message' => "Турнир '{$tournament->name}' успешно обновлен!",
            'tournament' => $tournament
        ]);
    }
    public function destroy($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournamentName = $tournament->name;
        $tournament->delete();

        return response()->json([
            'message' => "Турнир '{$tournamentName}' успешно удален!"
        ]);
    }
}
