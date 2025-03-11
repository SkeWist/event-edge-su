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
        'views_count',
        'user_id',
        'game_id',
        'stage_id',
    ];

    protected $hidden = ['user_id', 'game_id', 'stage_id', 'created_at', 'updated_at'];

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
