<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','logo', 'description'
    ];

    /**
     * Связь с турнирами (одна игра может быть в нескольких турнирах).
     */
    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}
