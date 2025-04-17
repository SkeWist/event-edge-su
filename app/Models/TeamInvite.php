<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'expires_at',
        'status',
        'message',
    ];
    protected $dates = ['expires_at'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
