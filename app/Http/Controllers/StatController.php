<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\GameMatch;
use Carbon\Carbon;
class StatController extends Controller
{
    public function overview()
    {
        return response()->json([
            'total_users' => User::count(),
            'new_users_this_month' => User::whereMonth('created_at', Carbon::now()->month)->count(),
            'total_tournaments' => Tournament::count(),
            'active_tournaments' => Tournament::where('status', 'active')->count(),
            'finished_tournaments' => Tournament::where('status', 'finished')->count(),
            'total_teams' => Team::count(),
            'total_matches' => GameMatch::count(),
            'user_activity' => [
                'labels' => ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
                'data' => [5, 10, 7, 8, 6, 12, 14], // Пример данных
            ],
            'popular_tournaments' => Tournament::orderByDesc('views_count')
                ->take(5)
                ->get(['name', 'views_count']),
        ]);
    }

    // Статистика по турнирам
    public function tournamentStats()
    {
        return response()->json([
            'total_tournaments' => Tournament::count(),
            'active_tournaments' => Tournament::where('status', 'active')->count(),
            'popular_tournaments' => Tournament::orderByDesc('views_count')
                ->take(5)
                ->get(['name', 'views_count']),
        ]);
    }
}
