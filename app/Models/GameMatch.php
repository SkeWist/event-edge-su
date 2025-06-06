<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMatch extends Model
{
    protected $fillable = [
        'tournament_id', 'team_1_id', 'team_2_id',
        'match_date', 'result', 'status', 'stage_id', 'winner_team_id'
    ];
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function teamA()
    {
        return $this->belongsTo(Team::class, 'team_1_id'); // Предположим, что команда A связана с team_1_id
    }

    public function teamB()
    {
        return $this->belongsTo(Team::class, 'team_2_id'); // Предположим, что команда B связана с team_2_id
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
    // Указание, что поля с датой нужно автоматически преобразовывать
    protected $dates = ['match_date'];

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
    public function winnerTeam()
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
    public function scopeActive($query)
    {
        return $query->where('match_date', '>=', now());
    }
    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournaments_basket')
            ->withPivot('result') // Указываем, что есть дополнительное поле "result"
            ->withTimestamps();   // Включаем автоматическое добавление временных меток
    }
    public function tournamentBaskets()
    {
        return $this->hasMany(TournamentBasket::class);
    }
}
