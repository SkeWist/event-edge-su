<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    // Просмотр всех команд
    public function index()
    {
        $teams = Team::all();
        return response()->json($teams);
    }

    // Просмотр одной команды
    public function show($id)
    {
        $team = Team::findOrFail($id);
        return response()->json($team);
    }

    // Создание новой команды
    public function store(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:teams,name',
            'captain_id' => 'required|exists:users,id', // Проверка существования капитана
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Создание команды
        $team = Team::create([
            'name' => $request->name,
            'captain_id' => $request->captain_id, // Здесь передаем captain_id
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Команда успешно создана!',
            'team' => $team
        ], 201);
    }
    // Редактирование команды
    public function update(Request $request, $id)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255|unique:teams,name,' . $id,
            'captain_id' => 'nullable|exists:users,id', // Проверка существования капитана
            'status' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Находим команду по ID
        $team = Team::findOrFail($id);

        // Обновляем команду
        $team->update($request->only(['name', 'captain_id', 'status']));

        return response()->json([
            'message' => 'Команда успешно обновлена!',
            'team' => $team
        ]);
    }

    // Удаление команды
    public function destroy($id)
    {
        // Находим команду по ID
        $team = Team::findOrFail($id);

        // Удаляем команду
        $team->delete();

        return response()->json([
            'message' => 'Команда успешно удалена!'
        ]);
    }
}
