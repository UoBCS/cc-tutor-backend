<?php

namespace App\Api\Users\Models;

use App\Api\Lessons\Models\Lesson;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'teacher', 'class_invitation_token', 'email_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'email_token',
        'pivot',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    public function courses()
    {
        return $this->belongsToMany('App\Api\Courses\Models\Course', 'user_course')->withPivot('lesson_id');
    }

    public function isSubscribedTo($cid)
    {
        return $this->courses()->where('course_id', $cid)->first() !== null;
    }

    public function currentLesson($cid)
    {
        return Lesson::find($this->courses()->where('course_id', $cid)->first()->pivot->lesson_id);
    }

    public function nextLesson($cid)
    {
        $currentLesson = $this->currentLesson($cid);
        $newIndex = $currentLesson->index + 1;

        return Lesson::where('index', $newIndex)->where('course_id', $cid)->first();
    }

    public function users()
    {
        return $this->teacher
            ? $this->belongsToMany('App\Api\Users\Models\User', 'user_user', 'teacher_id', 'student_id')
            : $this->belongsToMany('App\Api\Users\Models\User', 'user_user', 'student_id', 'teacher_id');
    }

    public function assignments()
    {
        return $this->teacher
            ? $this->hasMany('App\Api\Assignments\Models\Assignment', 'teacher_id')
            : $this->belongsToMany('App\Api\Assignments\Models\Assignment', 'user_assignment', 'user_id', 'assignment_id');
    }

    public function scopeTeachers($query)
    {
        return $query->where('teacher', 1);
    }

    public function scopeStudents($query)
    {
        return $query->where('teacher', 0);
    }
}
