<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'stage_type_id',
        'rounds'
    ];

    /**
     * Связь с типом этапа (Many-to-One).
     */
    public function stageType()
    {
        return $this->belongsTo(StageType::class);
    }
}
