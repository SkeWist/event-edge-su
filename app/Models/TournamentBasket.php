<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentBasket extends Model
{
    protected $fillable = [
        'tournament_id',
        'game_match_id',
        'status',
        'winner_team_id',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function gameMatch()
    {
        return $this->belongsTo(GameMatch::class);
    }

    public function teamA()
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    public function teamB()
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function winnerTeam()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
}
