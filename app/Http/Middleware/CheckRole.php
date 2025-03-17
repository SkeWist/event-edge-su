<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Проверка, что пользователь авторизован
        if (Auth::check()) {
            $user = Auth::user();

            // Проверка на роль пользователя
            if ($user->role_id == $role) {
                return $next($request); // Если роль совпадает, продолжаем выполнение запроса
            }
        }

        // Если роль не соответствует, возвращаем ошибку доступа
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
