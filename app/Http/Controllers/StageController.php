<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stage\StoreStageRequest;
use App\Http\Requests\Stage\UpdateStageRequest;
use App\Models\Stage;
use App\Models\StageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class StageController extends Controller
{
    /**
     * Просмотр всех этапов
     */
    public function index(): JsonResponse
    {
        $stages = Stage::with('stageType')->get();  // Загрузка этапов с типами

        // Преобразуем каждый этап, чтобы заменить id на имя типа
        $stages = $stages->map(function ($stage) {
            $stage->stage_type_name = $stage->stageType->name;  // Добавляем имя типа стадии
            unset($stage->stageType);  // Убираем связанный объект stageType
            return $stage;
        });

        return response()->json($stages);
    }

    /**
     * Просмотр одного этапа по ID
     */
    public function show(int $id): JsonResponse
    {
        $stage = Stage::with('stageType')->findOrFail($id);  // Загрузка этапа с типом

        // Заменяем id на имя типа стадии
        $stage->stage_type_name = $stage->stageType->name;
        unset($stage->stageType);  // Убираем связанный объект stageType

        return response()->json($stage);
    }

    /**
     * Создание нового этапа
     */
    public function store(StoreStageRequest $request): JsonResponse
    {
        $stage = Stage::create($request->validated());

        return response()->json([
            'message' => 'Этап успешно создан!',
            'stage' => $stage
        ], 201);
    }

    /**
     * Редактирование этапа
     */
    public function update(UpdateStageRequest $request, int $id): JsonResponse
    {
        // Находим этап по ID
        $stage = Stage::findOrFail($id);

        // Обновляем этап
        $stage->update($request->validated());

        return response()->json([
            'message' => 'Этап успешно обновлен!',
            'stage' => $stage
        ]);
    }

    /**
     * Удаление этапа
     */
    public function destroy(int $id): JsonResponse
    {
        // Находим этап по ID
        $stage = Stage::findOrFail($id);

        // Удаляем этап
        $stage->delete();

        return response()->json([
            'message' => 'Этап успешно удален!'
        ]);
    }
}
