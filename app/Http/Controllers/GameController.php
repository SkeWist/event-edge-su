<?php

namespace App\Http\Controllers;

use App\Http\Requests\Game\StoreGameRequest;
use App\Http\Requests\Game\UpdateGameRequest;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    /**
     * Просмотр списка игр.
     */
    public function index(): JsonResponse
    {
        $games = Game::all(); // Получаем все игры
        return response()->json($games);
    }

    /**
     * Просмотр одной игры.
     */
    public function show(int $id): JsonResponse
    {
        $game = Game::findOrFail($id); // Находим игру по ID
        return response()->json($game);
    }

    /**
     * Создание новой игры.
     */
    public function store(StoreGameRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('gameLogo', 'public');
            $validated['logo'] = $logoPath;
        }

        $game = Game::create($validated);

        return response()->json([
            'message' => 'Игра успешно добавлена!',
            'game' => $game,
        ], 201); // Ответ с созданной игрой
    }

    /**
     * Редактирование игры.
     */
    public function update(UpdateGameRequest $request, int $id): JsonResponse
    {
        $game = Game::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('logo')) {
            // Удаляем старое изображение, если оно существует
            if ($game->logo && file_exists(public_path($game->logo))) {
                unlink(public_path($game->logo));  // Удаление старого изображения
            }

            $logoPath = $request->file('logo')->store('gameLogo', 'public');
            $validated['logo'] = $logoPath;
        }

        $game->update($validated);

        return response()->json([
            'message' => 'Игра успешно редактирована!',
            'game' => $game,
        ]);
    }

    /**
     * Удаление игры.
     */
    public function destroy(int $id): JsonResponse
    {
        // Поиск игры по ID
        $game = Game::findOrFail($id);

        // Удаление изображения, если оно существует
        if ($game->logo && file_exists(public_path($game->logo))) {
            unlink(public_path($game->logo));  // Удаление файла изображения
        }

        // Удаление записи игры
        $game->delete();

        return response()->json([
            'message' => 'Игра успешно удалена!',
        ], 204); // Ответ без содержимого, код 204 - удалено
    }
}
