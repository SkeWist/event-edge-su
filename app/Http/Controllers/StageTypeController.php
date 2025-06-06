<?php

namespace App\Http\Controllers;

use App\Models\StageType;
use Illuminate\Http\Request;

class StageTypeController extends Controller
{
    /**
     * Создание нового типа этапа.
     */
    public function index()
    {
        // Получаем все типы этапов
        $stageTypes = StageType::all();

        return response()->json([
            'stage_types' => $stageTypes
        ]);
    }
    public function store(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Создание типа этапа
        $stageType = StageType::create([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'message' => 'Тип этапа успешно добавлен!',
            'stage_type' => $stageType
        ], 201); // Ответ с созданным объектом
    }

    /**
     * Редактирование типа этапа.
     */
    public function update(Request $request, $id)
    {
        // Валидация входных данных
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Поиск типа этапа по ID
        $stageType = StageType::findOrFail($id);

        // Обновление данных
        $stageType->update([
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'message' => 'Тип этапа успешно редактирован!',
            'stage_type' => $stageType
        ]); // Ответ с обновленным объектом
    }

    /**
     * Удаление типа этапа.
     */
    public function destroy($id)
    {
        // Поиск типа этапа по ID
        $stageType = StageType::findOrFail($id);

        // Удаление
        $stageType->delete();

        return response()->json([
            'message' => 'Тип этапа успешно удален!'
        ], 204); // Ответ без содержимого, код 204 - удалено
    }
}
