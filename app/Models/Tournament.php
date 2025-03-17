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
    ];

    protected $hidden = ['user_id', 'game_id', 'stage_id', 'created_at', 'updated_at', 'status'];

    protected $appends = ['status_name']; // Добавляем status_name в JSON

    public function getStatusNameAttribute()
    {
        $statuses = [
            'pending' => 'Ожидание',
            'ongoing' => 'В процессе',
            'completed' => 'Завершен',
        ];

        return $statuses[$this->status] ?? 'Неизвестно';
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
}
