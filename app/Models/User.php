<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Атрибуты, которые можно массово заполнять.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // Имя пользователя
        'email', // Email
        'password', // Пароль
        'role_id', // ID роли
        'avatar', // URL аватара
    ];

    /**
     * Атрибуты, которые нужно скрыть для массивов.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', // Пароль
        'remember_token', // Токен для "запомнить меня"
    ];

    /**
     * Атрибуты, которые должны быть преобразованы к типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Преобразование даты подтверждения email
        'is_active' => 'boolean', // Преобразование статуса активности в boolean
    ];

    /**
     * Связь с моделью Role (роль пользователя).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user');
    }

    public function invitations()
    {
        return $this->hasMany(TeamInvite::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function news()
    {
        return $this->hasMany(NewsFeed::class);
    }
    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}
