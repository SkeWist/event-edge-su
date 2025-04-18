<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentRequest extends Model
{
    use HasFactory;

    // Указываем, какие поля можно массово заполнять
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'game_id',
        'stage_id',
        'status',
        'user_id',
        'teams',
        'image'
    ];

    // Тип поля teams (это JSON)
    protected $casts = [
        'teams' => 'array',
    ];

    // Связь с таблицей игр
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // Связь с таблицей стадий
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    // Связь с пользователем (организатором турнира)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
