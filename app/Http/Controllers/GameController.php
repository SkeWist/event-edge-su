<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Просмотр списка игр.
     */
    public function index()
    {
        $games = Game::all(); // Получаем все игры
        return response()->json($games);
    }

    /**
     * Просмотр одной игры.
     */
    public function show($id)
    {
        $game = Game::findOrFail($id); // Находим игру по ID
        return response()->json($game);
    }

    /**
     * Создание новой игры.
     */
    public function store(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Валидируем, что это изображение
            'description' => 'required|string',
        ]);

        // Загружаем изображение
        $logoPath = $request->file('logo')->store('gameLogo', 'public'); // Сохраняем изображение в папку storage/app/public/gameLogo

        // Создание игры
        $game = Game::create([
            'name' => $request->input('name'),
            'logo' => $logoPath, // Сохраняем путь к изображению в базе данных
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'message' => 'Игра успешно добавлена!',
            'game' => $game,
        ], 201); // Ответ с созданной игрой
    }
    /**
     * Редактирование игры.
     */
    public function update(Request $request, $id)
    {
        // Валидация входных данных
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Логотип может быть не указан
            'description' => 'required|string',
        ]);

        // Поиск игры по ID
        $game = Game::findOrFail($id);

        // Если есть новый файл лого, обрабатываем его
        if ($request->hasFile('logo')) {
            // Удаляем старое изображение, если оно существует
            if ($game->logo && file_exists(public_path($game->logo))) {
                unlink(public_path($game->logo));  // Удаление старого изображения
            }

            // Загружаем новый логотип
            $logoPath = $request->file('logo')->store('public/gameLogo');
            $logoPath = str_replace('public/', 'storage/', $logoPath); // Преобразуем путь в публичный
        } else {
            // Если лого не обновляется, оставляем прежний путь
            $logoPath = $game->logo;
        }

        // Обновление данных игры
        $game->update([
            'name' => $request->input('name'),
            'logo' => $logoPath,
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'message' => 'Игра успешно редактирована!',
            'game' => $game,
        ]);
    }
    /**
     * Удаление игры.
     */
    public function destroy($id)
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
