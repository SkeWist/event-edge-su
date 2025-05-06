<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamTournamentRequest extends Model
{
    protected $fillable = [
        'team_id',
        'tournament_id',
        'status'
    ];

    /**
     * Получить команду, подавшую заявку
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Получить турнир, на который подана заявка
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }
} 