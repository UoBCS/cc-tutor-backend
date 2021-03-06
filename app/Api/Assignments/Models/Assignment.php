<?php

namespace App\Api\Assignments\Models;

use App\Infrastructure\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Assignment extends Model
{
    use Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at', 'extra'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function teacher()
    {
        return $this->belongsTo('App\Api\Users\Models\User', 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany('App\Api\Users\Models\User', 'user_assignment', 'assignment_id', 'user_id')->withPivot('submission_date');
    }
}
