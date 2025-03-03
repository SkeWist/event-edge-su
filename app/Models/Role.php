<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code_role',
    ];

    /**
     * Связь с пользователями (одна роль может принадлежать многим пользователям).
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
