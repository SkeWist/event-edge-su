<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'captain_id',
        'status',
    ];

    /**
     * Связь с капитаном команды (One-to-One).
     */
    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id'); // Связь с пользователем (капитаном)
    }

    /**
     * Связь с участниками команды (Many-to-Many).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'participants');
    }
    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_teams');
    }

}
