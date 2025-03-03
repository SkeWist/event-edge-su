<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsFeed extends Model
{
    protected $fillable = ['title', 'description', 'status', 'published_at', 'user_id'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
