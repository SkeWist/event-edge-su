<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    protected $fillable = [
        'game_id',
        'team_1_id',
        'team_2_id',
        'match_date',
        'status',
        'winner_team_id',
        'stage_id',
        'result'
    ];

    // Указание, что поля с датой нужно автоматически преобразовывать
    protected $dates = ['match_date'];

    /**
     * Связь с игрой.
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Связь с первой командой.
     */
    public function team1()
    {
        return $this->belongsTo(Team::class, 'team_1_id');
    }

    /**
     * Связь со второй командой.
     */
    public function team2()
    {
        return $this->belongsTo(Team::class, 'team_2_id');
    }

    /**
     * Связь с победившей командой.
     */
    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    /**
     * Связь с этапом турнира.
     */
    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * Связь с участниками матча (можно использовать через таблицу match_participant).
     */
    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'match_participant', 'game_match_id', 'participant_id');
    }

    /**
     * Scope для фильтрации матчей, которые ещё не начались.
     */
    public function scopeActive($query)
    {
        return $query->where('match_date', '>=', now());
    }
}
