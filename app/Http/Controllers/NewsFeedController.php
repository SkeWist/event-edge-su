<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsFeed\StoreNewsFeedRequest;
use App\Http\Requests\NewsFeed\UpdateNewsFeedRequest;
use App\Models\NewsFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsFeedController extends Controller
{
    /**
     * Просмотр списка новостей с фильтрацией по статусу.
     */
  public function index(): JsonResponse
  {
    $newsFeeds = NewsFeed::with(['user', 'category'])
      ->when(request('status'), function ($query, $status) {
        $query->where('status', $status);
      })
      ->when(request('is_featured'), function ($query) {
        $query->where('is_featured', true);
      })
      ->orderBy('published_at', 'desc')
      ->get()
      ->map(function ($news) {
        return [
          'id' => $news->id,
          'title' => $news->title,
          'slug' => $news->slug,
          'description' => $news->description,
          'content' => $news->content,
          'status' => $news->status,
          'published_at' => $news->published_at,
          'image' => $news->image ? asset('storage/' . $news->image) : null,
          'is_featured' => $news->is_featured,
          'views_count' => $news->views_count,
          'meta_title' => $news->meta_title,
          'meta_description' => $news->meta_description,
          'author_name' => $news->user->name ?? 'Неизвестный автор',
          'category_name' => $news->category->name ?? null,
        ];
      });

    return response()->json($newsFeeds);
  }

    /**
     * Просмотр новости по slug.
     */
  public function show(string $slug): JsonResponse
  {
    $newsFeed = NewsFeed::with(['user', 'category'])
      ->where('slug', $slug)
      ->firstOrFail();

    // Увеличиваем счетчик просмотров
    $newsFeed->increment('views_count');

    return response()->json([
      'id' => $newsFeed->id,
      'title' => $newsFeed->title,
      'slug' => $newsFeed->slug,
      'description' => $newsFeed->description,
      'content' => $newsFeed->content,
      'status' => $newsFeed->status,
      'published_at' => $newsFeed->published_at,
      'archived_at' => $newsFeed->archived_at,
      'image' => $newsFeed->image ? asset('storage/' . $newsFeed->image) : null,
      'is_featured' => $newsFeed->is_featured,
      'views_count' => $newsFeed->views_count,
      'meta_title' => $newsFeed->meta_title,
      'meta_description' => $newsFeed->meta_description,
      'author' => [
        'name' => $newsFeed->user->name ?? 'Неизвестный автор',
        'id' => $newsFeed->user->id ?? null,
      ],
      'category' => $newsFeed->category ? [
        'name' => $newsFeed->category->name,
        'id' => $newsFeed->category->id,
      ] : null,
    ]);
  }

    /**
     * Создание новости.
     */
    public function store(StoreNewsFeedRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Генерация slug
        $validated['slug'] = Str::slug($validated['title']);

        // Установка текущего пользователя
        $validated['user_id'] = auth()->id();

        // Обработка изображения
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $newsFeed = NewsFeed::create($validated);

        return response()->json([
            'message' => 'Новость успешно создана!',
            'news_feed' => $newsFeed->load(['user', 'category']),
        ], 201);
    }

    /**
     * Редактирование новости.
     */
    public function update(UpdateNewsFeedRequest $request, int $id): JsonResponse
    {
        $newsFeed = NewsFeed::findOrFail($id);
        $validated = $request->validated();

        // Обновление slug при изменении заголовка
        if (isset($validated['title']) && $validated['title'] !== $newsFeed->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Обновление изображения
        if ($request->hasFile('image')) {
            // Удаляем старое изображение
            if ($newsFeed->image) {
                Storage::disk('public')->delete($newsFeed->image);
            }
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $newsFeed->update(array_filter($validated, fn($value) => $value !== null));

        return response()->json([
            'message' => 'Новость успешно обновлена!',
            'news_feed' => $newsFeed->fresh(['user', 'category']),
        ]);
    }

    /**
     * Мягкое удаление новости.
     */
    public function destroy(int $id): JsonResponse
    {
        $newsFeed = NewsFeed::findOrFail($id);

        // Удаляем изображение при удалении новости
        if ($newsFeed->image) {
            Storage::disk('public')->delete($newsFeed->image);
        }

        $newsFeed->delete();

        return response()->json([
            'message' => 'Новость успешно перемещена в архив!',
        ]);
    }

    /**
     * Восстановление новости из архива.
     */
    public function restore(int $id): JsonResponse
    {
        $newsFeed = NewsFeed::onlyTrashed()->findOrFail($id);
        $newsFeed->restore();

        return response()->json([
            'message' => 'Новость успешно восстановлена!',
            'news_feed' => $newsFeed,
        ]);
    }

    /**
     * Полное удаление новости.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $newsFeed = NewsFeed::onlyTrashed()->findOrFail($id);

        // Удаляем изображение
        if ($newsFeed->image) {
            Storage::disk('public')->delete($newsFeed->image);
        }

        $newsFeed->forceDelete();

        return response()->json([
            'message' => 'Новость полностью удалена!',
        ], 204);
    }
}
