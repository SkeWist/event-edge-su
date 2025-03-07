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
        $newsFeeds = NewsFeed::with('user')->get()
            ->makeHidden(['user_id', 'created_at', 'updated_at']);

        $newsFeeds->each(function ($news) {
            $news->author_name = $news->user->name ?? 'Неизвестный автор';
            unset($news->user);
        });

        return response()->json($newsFeeds);
    }
    public function show($id)
    {
        $newsFeed = NewsFeed::with('user')->findOrFail($id)
            ->makeHidden(['user_id', 'created_at', 'updated_at']);

        $newsFeed->author_name = $newsFeed->user->name ?? 'Неизвестный автор';
        unset($newsFeed->user);

        return response()->json($newsFeed);
    }
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
