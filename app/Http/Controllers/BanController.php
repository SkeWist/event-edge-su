<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\User;
use App\Http\Requests\Ban\BanUserRequest;
use App\Http\Requests\Ban\UpdateBanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BanController extends Controller
{
    public function index(Request $request)
    {
        $query = Ban::with(['user', 'bannedBy'])
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'active') {
                    $q->where(function ($q) {
                        $q->where('is_permanent', true)
                            ->orWhere('banned_until', '>', now());
                    });
                } elseif ($request->status === 'expired') {
                    $q->where('is_permanent', false)
                        ->where('banned_until', '<=', now());
                }
            })
            ->latest();

        $bans = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $bans
        ]);
    }

    public function banUser(BanUserRequest $request)
    {
        $user = User::findOrFail($request->user_id);

        if ($user->isBanned()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь уже заблокирован',
                'data' => [
                    'active_ban' => $user->activeBan
                ]
            ], 422);
        }

        try {
            $ban = DB::transaction(function () use ($request) {
                return Ban::create([
                    'user_id' => $request->user_id,
                    'banned_by' => Auth::id(),
                    'reason' => $request->reason,
                    'banned_until' => $request->banned_until,
                    'is_permanent' => in_array($request->is_permanent, [true, 1, '1', 'true'], true) ? true : false,
                ]);
            });

            return response()->json([
                'status' => 'Успешно',
                'message' => 'Пользователь успешно заблокирован',
                'data' => $ban->load(['user', 'bannedBy'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось заблокировать пользователя',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $ban = Ban::findOrFail($id);
            
            // Принудительно перезагружаем отношения
            $ban->load(['user', 'bannedBy']);
            
            // Проверяем, что отношения загружены
            if (!$ban->user || !$ban->bannedBy) {
                \Log::warning('Ban relationships not loaded properly', [
                    'ban_id' => $ban->id,
                    'user_id' => $ban->user_id,
                    'banned_by' => $ban->banned_by
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $ban->id,
                    'user' => $ban->user,
                    'banned_by' => $ban->bannedBy,
                    'reason' => $ban->reason,
                    'banned_until' => $ban->banned_until,
                    'is_permanent' => $ban->is_permanent,
                    'created_at' => $ban->created_at,
                    'updated_at' => $ban->updated_at
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Бан не найден'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error showing ban details', [
                'ban_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось загрузить детали бана'
            ], 500);
        }
    }

    public function update(UpdateBanRequest $request, $id)
    {
        try {
            // Ищем бан или выбрасываем 404
            $ban = Ban::findOrFail($id);
            
            $ban->update([
                'reason' => $request->reason,
                'banned_until' => $request->banned_until,
                'is_permanent' => in_array($request->is_permanent, [true, 1, '1', 'true'], true) ? true : false,
            ]);

            // Перезагружаем модель с отношениями
            $ban->refresh();
            $ban->load(['user', 'bannedBy']);

            return response()->json([
                'status' => 'success',
                'message' => 'Бан успешно обновлен',
                'data' => [
                    'id' => $ban->id,
                    'user' => $ban->user,
                    'banned_by' => $ban->bannedBy,
                    'reason' => $ban->reason,
                    'banned_until' => $ban->banned_until,
                    'is_permanent' => $ban->is_permanent,
                    'created_at' => $ban->created_at,
                    'updated_at' => $ban->updated_at
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Бан не найден'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating ban', [
                'ban_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось обновить бан'
            ], 500);
        }
    }

    public function destroy(Ban $ban)
    {
        $ban->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Бан успешно удален'
        ], 204);
    }

    public function unban(Ban $ban)
    {
        if (!$ban->isActive()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Этот бан уже не активен'
            ], 422);
        }

        $ban->update([
            'banned_until' => now(),
            'is_permanent' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Пользователь успешно разблокирован',
            'data' => $ban->load(['user', 'bannedBy'])
        ]);
    }
} 