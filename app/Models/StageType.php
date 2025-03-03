<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StageType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Связь с этапами (One-to-Many).
     */
    public function stages()
    {
        return $this->hasMany(Stage::class);
    }
}
