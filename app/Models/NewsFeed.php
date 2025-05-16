<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class NewsFeed extends Model
{
    use SoftDeletes; // Мягкое удаление

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'status',
        'published_at',
        'archived_at',
        'user_id',
        'category_id',
        'image',
        'meta_title',
        'meta_description',
        'is_featured',
    ];

    protected $dates = [
        'published_at',
        'archived_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    // Статусы для удобного доступа
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    // Автоматическое создание slug при сохранении
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->title);
        });

        static::updating(function ($model) {
            if ($model->isDirty('title')) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    // Связи
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(NewsCategory::class);
    }
}
