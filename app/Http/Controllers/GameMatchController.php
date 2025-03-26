<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\TournamentBasket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class GameMatchController extends Controller
{
    public function index()
{
    $matches = GameMatch::with(['game', 'teamA', 'teamB', 'stage'])->get()
        ->makeHidden(['game_id', 'team_1_id', 'team_2_id', 'created_at', 'updated_at']);

    $matches->each(function ($match) {
        $match->game_name = $match->game->name ?? 'Неизвестная игра';
        $match->team_1_name = $match->teamA->name ?? 'Неизвестная команда'; // Используем teamA
        $match->team_2_name = $match->teamB->name ?? 'Неизвестная команда'; // Используем teamB
        $match->stage_name = $match->stage->name ?? 'Этап не указан';

        unset($match->game, $match->teamA, $match->teamB, $match->stage); // Очищаем связи
    });

    return response()->json($matches);
}

/**
 * Просмотр матча по id.
 */
public function show($id)
{
    $match = GameMatch::with(['game', 'teamA', 'teamB', 'stage'])->findOrFail($id)
        ->makeHidden(['game_id', 'team_1_id', 'team_2_id', 'created_at', 'updated_at']);

    $match->game_name = $match->game->name ?? 'Неизвестная игра';
    $match->team_1_name = $match->teamA->name ?? 'Неизвестная команда'; // Используем teamA
    $match->team_2_name = $match->teamB->name ?? 'Неизвестная команда'; // Используем teamB
    $match->stage_name = $match->stage->name ?? 'Этап не указан';

    unset($match->game, $match->teamA, $match->teamB, $match->stage); // Очищаем связи

    return response()->json($match);
}
    /**
     * Создание нового матча.
     */
    public function store(Request $request)
{
    // Валидация данных
    $validator = Validator::make($request->all(), [
        'tournament_id' => 'required|exists:tournaments,id',
        'team_1_id' => 'required|exists:teams,id',
        'team_2_id' => 'required|exists:teams,id',
        'match_date' => 'required|date_format:Y-m-d\TH:i',
        'stage_id' => 'nullable|exists:stages,id',
        'status' => 'required|in:pending,completed,canceled',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    try {
        // Преобразование строки даты в объект Carbon
        $match_date = Carbon::createFromFormat('Y-m-d\TH:i', $request->match_date);

        // Преобразуем дату в формат, который можно сохранить в базе данных (Y-m-d H:i:s)
        $match_date = $match_date->format('Y-m-d H:i:s');
    } catch (\Exception $e) {
        return response()->json(['error' => 'Неверный формат даты и времени'], 400);
    }

    // Создание нового матча
    $match = GameMatch::create([
        'tournament_id' => $request->tournament_id,
        'team_1_id' => $request->team_1_id,
        'team_2_id' => $request->team_2_id,
        'match_date' => $match_date,
        'stage_id' => $request->stage_id,
        'status' => $request->status,
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
    // Проверяем, какие данные реально пришли
    \Log::info('Данные запроса:', $request->all());

    // Валидация
    $validator = Validator::make($request->all(), [
        'tournament_id' => 'nullable|exists:tournaments,id', // Сделали nullable
        'team_1_id' => 'nullable|exists:teams,id',
        'team_2_id' => 'nullable|exists:teams,id',
        'match_date' => 'nullable|date_format:Y-m-d H:i:s', // Изменили формат даты
        'stage_id' => 'nullable|exists:stages,id',
        'status' => 'required|in:pending,completed,canceled',
    ]);

    if ($validator->fails()) {
        \Log::error('Ошибка валидации: ', $validator->errors()->toArray());
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Находим матч
    $match = GameMatch::findOrFail($id);

    // Формируем данные для обновления
    $updateData = $request->only([
        'game_id', 'team_1_id', 'team_2_id', 'match_date', 'stage_id', 'status'
    ]);

    if ($request->has('match_date')) {
        $updateData['match_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $request->match_date)->format('Y-m-d H:i:s');
    }

    // Обновляем только ненулевые значения
    $match->update(array_filter($updateData, fn($value) => $value !== null));

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
