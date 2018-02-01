<?php

namespace App\Api\Courses\Models;

use App\Infrastructure\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Course extends Model
{
    use Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'created_at', 'updated_at',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Api\Users\Models\User', 'user_course');
    }

    public function lessons()
    {
        return $this->hasMany('App\Api\Lessons\Models\Lesson');
    }
}
