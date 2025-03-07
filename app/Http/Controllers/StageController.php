<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\StageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StageController extends Controller
{
    // Просмотр всех этапов
    public function index()
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
    // Просмотр одного этапа по ID
    public function show($id)
    {
        $stage = Stage::with('stageType')->findOrFail($id);  // Загрузка этапа с типом

        // Заменяем id на имя типа стадии
        $stage->stage_type_name = $stage->stageType->name;
        unset($stage->stageType);  // Убираем связанный объект stageType

        return response()->json($stage);
    }
    // Создание нового этапа
    public function store(Request $request)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date', // Если end_date указан, он должен быть после start_date
            'stage_type_id' => 'required|exists:stage_types,id',
            'rounds' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Создание нового этапа
        $stage = Stage::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'stage_type_id' => $request->stage_type_id,
            'rounds' => $request->rounds,
        ]);

        return response()->json([
            'message' => 'Этап успешно создан!',
            'stage' => $stage
        ], 201);
    }

    // Редактирование этапа
    public function update(Request $request, $id)
    {
        // Валидация данных
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date', // Если end_date указан, он должен быть после start_date
            'stage_type_id' => 'nullable|exists:stage_types,id',
            'rounds' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Находим этап по ID
        $stage = Stage::findOrFail($id);

        // Обновляем этап
        $stage->update($request->only(['name', 'start_date', 'end_date', 'stage_type_id', 'rounds']));

        return response()->json([
            'message' => 'Этап успешно обновлен!',
            'stage' => $stage
        ]);
    }

    // Удаление этапа
    public function destroy($id)
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
