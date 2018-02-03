<?php

namespace App\Api\CompilerConstructionAssistant\Services;

use App\Api\CompilerConstructionAssistant\Repositories\CompilerConstructionAssistantRepository;
use App\Api\Courses\Services\CourseService;
use App\Api\Lessons\Services\LessonService;
use Cz\Git\GitRepository;
use HerokuClient\Client as HerokuClient;
use Illuminate\Support\Facades\File;

class CompilerConstructionAssistantService
{
    private $repository;
    private $courseService;
    private $lessonService;
    private $appDirectory;
    private $appTestsDirectory;
    private $coursesDirectory;
    private $coursesTestsDirectory;

    public function __construct(
        CompilerConstructionAssistantRepository $repository,
        CourseService $courseService,
        LessonService $lessonService)
    {
        $this->repository = $repository;
        $this->courseService = $courseService;
        $this->lessonService = $lessonService;

        /*$this->heroku = new HerokuClient([
            'apiKey' => config('cc_tutor.heroku.api_key')
        ]);*/

        //$this->herokuAppName = config('cc_tutor.heroku.app_name');

        //$herokuGitUrl = $this->heroku->get('apps/' . $this->herokuAppName)->git_url;
        //$this->herokuRepo = new GitRepository(storage_path('app/cctutor'));

        $this->appDirectory = storage_path('app/cctutor/src/main/java/com/cctutor/app');
        $this->appTestsDirectory = storage_path('app/cctutor/src/test/java/com/cctutor/app');
        $this->coursesDirectory = joinPaths($this->appDirectory, 'courses');
        $this->coursesTestsDirectory = joinPaths($this->appTestsDirectory, 'courses');
    }

    public function subscribeToCourse($user, $cid)
    {
        // TODO: check if already related
        $lessons = $this->lessonService->getBy('course_id', $cid);
        $lesson  = $lessons->where('index', $lessons->min('index'))->first();
        $this->repository->relateUserAndCourse($user, $cid, ['lesson_id' => $lesson->id]);

        // Create course directory for user
        $course      = $this->courseService->getById($cid);
        $courseTitle = $this->normalizeName($course->title);
        $username    = $this->normalizeName($user->name);

        // Copy main package
        if (File::copyDirectory(joinPaths($this->coursesDirectory, $courseTitle), joinPaths($this->appDirectory, $username, $courseTitle))) {
            $this->changeFilesPackage(joinPaths($this->appDirectory, $username, $courseTitle), 'courses', $username);
        }

        // Copy test package
        if (File::copyDirectory(joinPaths($this->coursesTestsDirectory, $courseTitle), joinPaths($this->appTestsDirectory, $username, $courseTitle))) {
            $this->changeFilesPackage(joinPaths($this->appTestsDirectory, $username, $courseTitle), 'courses', $username);
        }

        return $this->getLessonData($user, $course, $lesson);
    }

    public function unsubscribeFromCourse($user, $cid)
    {
        // TODO: check if not attached
        $this->repository->unrelateUserAndCourse($user, $cid);

        // Delete course directory for user
        $course      = $this->courseService->getById($cid);
        $courseTitle = $this->normalizeName($course->title);
        $username    = $this->normalizeName($user->name);

        File::deleteDirectory(joinPaths($this->appDirectory, $username, $courseTitle));
        File::deleteDirectory(joinPaths($this->appTestsDirectory, $username, $courseTitle));

        return [
            'status' => true
        ];
    }

    public function getCurrentLesson($user, $cid)
    {
        $course = $this->courseService->getById($cid);
        $lesson = $user->currentLesson($cid);

        return $this->getLessonData($user, $course, $lesson);
    }

    public function saveLessonProgress($lid)
    {
        return null;
    }

    protected function changeFilesPackage($directory, $fromSegment, $toSegment)
    {
        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $content = $file->getContents();
            $content = preg_replace("/(package com\.cctutor\.app\.)(courses)(.*;)/", "$1$toSegment$3", $content);

            file_put_contents($file->getPathname(), $content);
        }
    }

    protected function getLessonData($user, $course, $lesson)
    {
        $username    = $this->normalizeName($user->name);
        $courseTitle = $this->normalizeName($course->title);
        $lessonTitle = $this->normalizeName($lesson->title);
        $outputData  = [
            'lesson_id'    => $lesson->id,
            'files'        => [],
            'instructions' => json_decode($lesson->instructions, true)
        ];

        $files = File::allFiles(joinPaths($this->appDirectory, $username, $courseTitle, $lessonTitle));
        foreach ($files as $file) {
            $outputData['files'][$file->getFilename()] = $file->getContents();
        }

        return $outputData;
    }

    private function normalizeName($str)
    {
        return strtolower(str_replace(' ', '', $str));
    }
}
