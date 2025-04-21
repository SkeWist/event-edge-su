<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'views_count',
        'user_id',
        'game_id',
        'stage_id',
        'status',
        'image',
    ];

    protected $hidden = ['user_id', 'game_id', 'stage_id', 'created_at', 'updated_at'];

    protected $appends = ['status_name']; // Добавляем status_name в JSON

    // Определяем список допустимых статусов
    public static $statuses = [
        'pending' => 'Ожидается',
        'ongoing' => 'В процессе',
        'completed' => 'Завершен',
        'canceled' => 'Отменен',
        'registrationOpen' => 'Регистрация открыта',
        'registrationClosed' => 'Регистрация закрыта',
    ];

    public static function getStatuses()
    {
        return static::$statuses;
    }

    public function getStatusNameAttribute()
    {
        return static::$statuses[$this->status] ?? 'Неизвестно';
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'tournament_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'tournament_teams', 'tournament_id', 'team_id');
    }
    public function gameMatches()
    {
        return $this->belongsToMany(GameMatch::class, 'tournaments_basket')
            ->withPivot('result', 'winner_team_id') // Связываем с полями промежуточной таблицы
            ->withTimestamps();    // Включаем автоматическое добавление временных меток
    }
    public function baskets()
    {
        return $this->hasMany(TournamentBasket::class);
    }
}
