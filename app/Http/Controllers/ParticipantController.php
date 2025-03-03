<?php

namespace App\Http\Controllers;

use App\Models\Participant;
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
