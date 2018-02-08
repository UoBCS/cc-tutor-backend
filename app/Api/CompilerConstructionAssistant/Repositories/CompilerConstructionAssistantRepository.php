<?php

namespace App\Api\CompilerConstructionAssistant\Repositories;

use App\Api\CompilerConstructionAssistant\Exceptions\UserAlreadySubscribedToCourse;
use App\Api\CompilerConstructionAssistant\Exceptions\UserNotSubscribedToCourse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

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
        $courseTitle = normalizeName($course->title);

        return joinPaths($this->coursesDirectory, $courseTitle);
    }

    public function getBaseCourseTestsPath($course)
    {
        $courseTitle = normalizeName($course->title);

        return joinPaths($this->coursesTestsDirectory, $courseTitle);
    }

    public function getCoursePath($course)
    {
        $courseTitle = normalizeName($course->title);
        $username    = normalizeName($this->user->name);

        return joinPaths($this->appDirectory, $username, $courseTitle);
    }

    public function getCourseTestsPath($course)
    {
        $courseTitle = normalizeName($course->title);
        $username    = normalizeName($this->user->name);

        return joinPaths($this->appTestsDirectory, $username, $courseTitle);
    }

    public function getLessonPath($course, $lesson)
    {
        $courseTitle = normalizeName($course->title);
        $lessonTitle = normalizeName($lesson->title);
        $username    = normalizeName($this->user->name);

        return joinPaths($this->appDirectory, $username, $courseTitle, $lessonTitle);
    }

    public function getLessonTestsPath($course, $lesson)
    {
        $courseTitle = normalizeName($course->title);
        $lessonTitle = normalizeName($lesson->title);
        $username    = normalizeName($this->user->name);

        return joinPaths($this->appTestsDirectory, $username, $courseTitle, $lessonTitle);
    }

    public function relateUserAndCourse($cid, $data = ['lesson_id' => 1])
    {
        $course = $this->user->courses()->where('course_id', $cid)->first();

        if ($course !== null) {
            throw new UserAlreadySubscribedToCourse();
        }

        $this->user->courses()->attach($cid, $data);
    }

    public function unrelateUserAndCourse($cid)
    {
        $course = $this->user->courses()->where('course_id', $cid)->first();

        if ($course === null) {
            throw new UserNotSubscribedToCourse();
        }

        $this->user->courses()->detach($cid);
    }

    public function getLessonData($course, $lesson)
    {
        $username    = normalizeName($this->user->name);
        $courseTitle = normalizeName($course->title);
        $lessonTitle = normalizeName($lesson->title);
        $outputData  = [
            'id'           => $lesson->id,
            'title'        => $lesson->title,
            'description'  => $lesson->description,
            'files'        => [],
            'instructions' => json_decode($lesson->instructions, true)
        ];

        $files = File::allFiles(joinPaths($this->appDirectory, $username, $courseTitle, $lessonTitle));
        foreach ($files as $file) {
            $outputData['files'][$file->getFilename()] = $file->getContents();
        }

        return $outputData;
    }
}
