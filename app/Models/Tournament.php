<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'team_id',
        'user_id',
        'game_id',
        'stage_id',
        'participants_id',
    ];

    /**
     * Связь с командой.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Связь с пользователем-организатором.
     */
    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связь с игрой.
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Связь с этапом турнира.
     */
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * Связь с участниками турнира.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'tournament_id');
    }
}
