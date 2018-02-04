<?php

namespace App\Api\CompilerConstructionAssistant\Repositories;

use App\Api\CompilerConstructionAssistant\Exceptions\UserAlreadySubscribedToCourse;
use App\Api\CompilerConstructionAssistant\Exceptions\UserNotSubscribedToCourse;
use Illuminate\Support\Facades\Auth;

class CompilerConstructionAssistantRepository
{
    private $appDirectory;
    private $appTestsDirectory;
    private $coursesDirectory;
    private $coursesTestsDirectory;
    private $user;

    public function __construct()
    {
        $this->appDirectory          = storage_path('app/cctutor/src/main/java/com/cctutor/app');
        $this->appTestsDirectory     = storage_path('app/cctutor/src/test/java/com/cctutor/app');
        $this->coursesDirectory      = joinPaths($this->appDirectory, 'courses');
        $this->coursesTestsDirectory = joinPaths($this->appTestsDirectory, 'courses');
        $this->user                  = Auth::user();
    }

    public function getBaseCoursePath($course)
    {
        $courseTitle = $this->normalizeName($course->title);

        return joinPaths($this->coursesDirectory, $courseTitle);
    }

    public function getBaseCourseTestsPath($course)
    {
        $courseTitle = $this->normalizeName($course->title);

        return joinPaths($this->coursesTestsDirectory, $courseTitle);
    }

    public function getCoursePath($course)
    {
        $courseTitle = $this->normalizeName($course->title);
        $username    = $this->normalizeName($this->user->name);

        return joinPaths($this->appDirectory, $username, $courseTitle);
    }

    public function getCourseTestsPath($course)
    {
        $courseTitle = $this->normalizeName($course->title);
        $username    = $this->normalizeName($this->user->name);

        return joinPaths($this->appTestsDirectory, $username, $courseTitle);
    }

    public function getLessonPath($course, $lesson)
    {
        $courseTitle = $this->normalizeName($course->title);
        $lessonTitle = $this->normalizeName($lesson->title);
        $username    = $this->normalizeName($this->user->name);

        return joinPaths($this->appTestsDirectory, $username, $courseTitle, $lessonTitle);
    }

    public function getLessonTestsPath($course, $lesson)
    {

    }

    public function relateUserAndCourse($user, $cid, $data = ['lesson_id' => 1])
    {
        $course = $user->courses()->where('id', $cid)->first();

        if ($course !== null) {
            throw new UserAlreadySubscribedToCourse();
        }

        $user->courses()->attach($cid, $data);
    }

    public function unrelateUserAndCourse($user, $cid)
    {
        $course = $user->courses()->where('id', $cid)->first();

        if ($course === null) {
            throw new UserNotSubscribedToCourse();
        }

        $user->courses()->detach($cid);
    }
}
