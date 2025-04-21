<?php

namespace App\Http\Controllers;

use App\Http\Requests\StageType\StoreStageTypeRequest;
use App\Http\Requests\StageType\UpdateStageTypeRequest;
use App\Models\StageType;
use Illuminate\Http\JsonResponse;

class StageTypeController extends Controller
{
    /**
     * Получение списка типов этапов
     */
    public function index(): JsonResponse
    {
        $stageTypes = StageType::select('id', 'name')->get();

        return response()->json([
            'message' => 'Список типов этапов успешно получен.',
            'data' => $stageTypes
        ]);
    }

    /**
     * Создание нового типа этапа
     */
    public function store(StoreStageTypeRequest $request): JsonResponse
    {
        $stageType = StageType::create($request->validated());

        return response()->json([
            'message' => 'Тип этапа успешно добавлен!',
            'stage_type' => $stageType
        ], 201);
    }

    /**
     * Редактирование типа этапа
     */
    public function update(UpdateStageTypeRequest $request, int $id): JsonResponse
    {
        $stageType = StageType::findOrFail($id);
        $stageType->update($request->validated());

        return response()->json([
            'message' => 'Тип этапа успешно редактирован!',
            'stage_type' => $stageType
        ]);
    }

    /**
     * Удаление типа этапа
     */
    public function destroy(int $id): JsonResponse
    {
        $stageType = StageType::findOrFail($id);
        $stageType->delete();

        return response()->json([
            'message' => 'Тип этапа успешно удален!'
        ], 204);
    }
}
