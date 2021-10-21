<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'slug', 
        'order'
    ];

    public $timestamps = false;

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ステータス削除時に、関連タスクを削除する
     */
    public static function boot ()
    {
        parent::boot ();

        static::deleting(function ($status) {
            $status->tasks()->delete();
        });
    }
}
