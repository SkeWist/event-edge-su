<?php

namespace App\Http\Controllers;

use App\Models\NewsFeed;
use Illuminate\Http\Request;

class NewsFeedController extends Controller
{
    /**
     * Просмотр списка новостей.
     */
    public function index()
    {
        $newsFeeds = NewsFeed::all(); // Получаем все новости
        return response()->json($newsFeeds);
    }

    /**
     * Просмотр одной новости.
     */
    public function show($id)
    {
        $newsFeed = NewsFeed::findOrFail($id); // Находим новость по ID
        return response()->json($newsFeed);
    }

    /**
     * Создание новости.
     */
    public function store(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'published_at' => 'nullable|date',
            'user_id' => 'required|exists:users,id', // Проверяем существование пользователя
        ]);

        // Создание новости
        $newsFeed = NewsFeed::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'published_at' => $request->input('published_at'),
            'user_id' => $request->input('user_id'),
        ]);

        return response()->json([
            'message' => 'Новость успешно добавлена!',
            'news_feed' => $newsFeed,
        ], 201); // Ответ с созданным объектом
    }

    /**
     * Редактирование новости.
     */
    public function update(Request $request, $id)
    {
        // Валидация входных данных
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'published_at' => 'nullable|date',
            'user_id' => 'required|exists:users,id', // Проверяем существование пользователя
        ]);

        // Поиск новости по ID
        $newsFeed = NewsFeed::findOrFail($id);

        // Обновление данных
        $newsFeed->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'published_at' => $request->input('published_at'),
            'user_id' => $request->input('user_id'),
        ]);

        return response()->json([
            'message' => 'Новость успешно редактирована!',
            'news_feed' => $newsFeed,
        ]);
    }

    /**
     * Удаление новости.
     */
    public function destroy($id)
    {
        // Поиск новости по ID
        $newsFeed = NewsFeed::findOrFail($id);

        // Удаление
        $newsFeed->delete();

        return response()->json([
            'message' => 'Новость успешно удалена!',
        ], 204); // Ответ без содержимого, код 204 - удалено
    }
}
