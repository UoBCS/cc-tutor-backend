<?php

namespace App\Api\Lessons\Models;

use App\Infrastructure\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Lesson extends Model
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
        'created_at', 'updated_at',
    ];

    public function course()
    {
        return $this->belongsTo('App\Api\Courses\Models\Course');
    }
}
