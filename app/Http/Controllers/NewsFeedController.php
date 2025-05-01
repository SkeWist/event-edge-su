<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsFeed\StoreNewsFeedRequest;
use App\Http\Requests\NewsFeed\UpdateNewsFeedRequest;
use App\Models\NewsFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class NewsFeedController extends Controller
{
    /**
     * Просмотр списка новостей.
     */
    public function index(): JsonResponse
    {
        $newsFeeds = NewsFeed::with('user')->get()
            ->makeHidden(['user_id', 'created_at', 'updated_at']);

        $newsFeeds->each(function ($news) {
            $news->author_name = $news->user->name ?? 'Неизвестный автор';
            unset($news->user);
        });

        return response()->json($newsFeeds);
    }

    /**
     * Просмотр новости по id.
     */
    public function show(int $id): JsonResponse
    {
        $newsFeed = NewsFeed::with('user')->findOrFail($id)
            ->makeHidden(['user_id', 'created_at', 'updated_at']);

        $newsFeed->author_name = $newsFeed->user->name ?? 'Неизвестный автор';
        unset($newsFeed->user);

        return response()->json($newsFeed);
    }

    /**
     * Создание новости.
     */
    public function store(StoreNewsFeedRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news_images', 'public');
            $validated['image'] = $path;
        }

        $newsFeed = NewsFeed::create($validated);

        return response()->json([
            'message' => 'Новость успешно добавлена!',
            'news_feed' => $newsFeed,
        ], 201);
    }

    /**
     * Редактирование новости.
     */
    public function update(UpdateNewsFeedRequest $request, int $id): JsonResponse
    {
        $newsFeed = NewsFeed::findOrFail($id);
        $validated = $request->validated();

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete old image if exists
            $newsFeed->deleteImage();
            
            // Store new image
            $path = $request->file('image')->store('news_images', 'public');
            $validated['image'] = $path;
        }

        $newsFeed->update(array_filter($validated, fn($value) => $value !== null));

        return response()->json([
            'message' => 'Новость успешно редактирована!',
            'news_feed' => $newsFeed,
        ]);
    }

    /**
     * Удаление новости.
     */
    public function destroy(int $id): JsonResponse
    {
        $newsFeed = NewsFeed::findOrFail($id);
        
        // Delete associated image
        $newsFeed->deleteImage();
        
        $newsFeed->delete();

        return response()->json([
            'message' => 'Новость успешно удалена!'
        ]);
    }
}
