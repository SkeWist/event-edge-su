<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ban extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'banned_by',
        'reason',
        'banned_until',
        'is_permanent',
    ];

    protected $casts = [
        'banned_until' => 'datetime',
        'is_permanent' => 'boolean',
    ];

    // Добавляем видимые поля при сериализации
    protected $visible = [
        'id',
        'user_id',
        'banned_by',
        'reason',
        'banned_until',
        'is_permanent',
        'created_at',
        'updated_at',
        'user',
        'bannedBy'
    ];

    // Добавляем автоматическую загрузку отношений
    protected $with = ['user', 'bannedBy'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by', 'id');
    }

    public function isActive(): bool
    {
        if ($this->is_permanent) {
            return true;
        }

        return $this->banned_until && $this->banned_until->isFuture();
    }
} 