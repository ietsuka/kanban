<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function statuses()
    {
        return $this->hasMany(Status::class)->orderBy('order');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->statuses()->createMany([
                [
                    'title' => '未処理',
                    'slug' => 'backlog',
                    'order' => 1
                ],
                [
                    'title' => '着手',
                    'slug' => 'up_next',
                    'order' => 2
                ],
                [
                    'title' => '進行中',
                    'slug' => 'progress',
                    'order' => 3
                ],
                [
                    'title' => '完了',
                    'slug' => 'done',
                    'order' => 4
                ],
                [
                    'title' => '保留',
                    'slug' => 'on_hold',
                    'order' => 5
                ]
            ]);
        });
    }
}
