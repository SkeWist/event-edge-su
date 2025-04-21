<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stage\StoreStageRequest;
use App\Http\Requests\Stage\UpdateStageRequest;
use App\Models\Stage;
use App\Models\StageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

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
            $stage->start_date = Carbon::parse($stage->start_date)->format('Y-m-d H:i');
            $stage->end_date = Carbon::parse($stage->end_date)->format('Y-m-d H:i');
            $stage->created_at = Carbon::parse($stage->created_at)->format('Y-m-d H:i');
            $stage->updated_at = Carbon::parse($stage->updated_at)->format('Y-m-d H:i');
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
        $stage->start_date = Carbon::parse($stage->start_date)->format('Y-m-d H:i');
        $stage->end_date = Carbon::parse($stage->end_date)->format('Y-m-d H:i');
        $stage->created_at = Carbon::parse($stage->created_at)->format('Y-m-d H:i');
        $stage->updated_at = Carbon::parse($stage->updated_at)->format('Y-m-d H:i');
        unset($stage->stageType);  // Убираем связанный объект stageType

        return response()->json($stage);
    }

    /**
     * Создание нового этапа
     */
    public function store(StoreStageRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Преобразуем даты в формат MySQL
        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::createFromFormat('d.m.Y H:i:s', $data['start_date'])
                ->format('Y-m-d H:i:s');
        }
        
        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::createFromFormat('d.m.Y H:i:s', $data['end_date'])
                ->format('Y-m-d H:i:s');
        }

        $stage = Stage::create($data);

        $responseStage = $stage->toArray();
        $responseStage['start_date'] = Carbon::parse($stage->start_date)->format('Y-m-d H:i');
        $responseStage['end_date'] = Carbon::parse($stage->end_date)->format('Y-m-d H:i');
        $responseStage['created_at'] = Carbon::parse($stage->created_at)->format('Y-m-d H:i');
        $responseStage['updated_at'] = Carbon::parse($stage->updated_at)->format('Y-m-d H:i');

        return response()->json([
            'message' => 'Этап успешно создан!',
            'stage' => $responseStage
        ], 201);
    }

    /**
     * Редактирование этапа
     */
    public function update(UpdateStageRequest $request, int $id): JsonResponse
    {
        // Находим этап по ID
        $stage = Stage::findOrFail($id);

        $data = $request->validated();
        
        // Преобразуем даты в формат MySQL
        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::createFromFormat('d.m.Y H:i:s', $data['start_date'])
                ->format('Y-m-d H:i:s');
        }
        
        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::createFromFormat('d.m.Y H:i:s', $data['end_date'])
                ->format('Y-m-d H:i:s');
        }

        // Обновляем этап
        $stage->update($data);

        $responseStage = $stage->toArray();
        $responseStage['start_date'] = Carbon::parse($stage->start_date)->format('Y-m-d H:i');
        $responseStage['end_date'] = Carbon::parse($stage->end_date)->format('Y-m-d H:i');
        $responseStage['created_at'] = Carbon::parse($stage->created_at)->format('Y-m-d H:i');
        $responseStage['updated_at'] = Carbon::parse($stage->updated_at)->format('Y-m-d H:i');

        return response()->json([
            'message' => 'Этап успешно обновлен!',
            'stage' => $responseStage
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
