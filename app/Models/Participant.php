<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'tournament_id',
    ];

    /**
     * Связь с пользователем.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с командой (может быть null, если пользователь участвует без команды).
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Связь с турниром.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
}
