<?php

namespace App\Api\CompilerConstructionAssistant\Services;

use App\Api\CompilerConstructionAssistant\Exceptions\LessonNotFoundException;
use App\Api\CompilerConstructionAssistant\Exceptions\UserNotSubscribedToCourse;
use App\Api\CompilerConstructionAssistant\Repositories\CompilerConstructionAssistantRepository;
use App\Api\Courses\Services\CourseService;
use App\Api\Lessons\Services\LessonService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception as SymfonyException;

class CompilerConstructionAssistantService
{
    private $repository;
    private $courseService;
    private $lessonService;

    public function __construct(
        CompilerConstructionAssistantRepository $repository,
        CourseService $courseService,
        LessonService $lessonService)
    {
        $this->repository = $repository;
        $this->courseService = $courseService;
        $this->lessonService = $lessonService;
    }

    public function subscribeToCourse($user, $cid)
    {
        $lessons = $this->lessonService->getBy('course_id', $cid);
        $lesson  = $lessons->where('index', $lessons->min('index'))->first();
        $this->repository->relateUserAndCourse($user, $cid, ['lesson_id' => $lesson->id]);

        // Create course directory for user
        $course      = $this->courseService->getById($cid);
        $username    = $this->normalizeName($user->name);

        // Copy main package
        $coursePath = $this->repository->getCoursePath($course);
        if (Storage::copyDirectory($this->repository->getBaseCoursePath($course), $coursePath)) {
            $this->changeFilesPackage($coursePath, 'courses', $username);
        }

        // Copy test package
        $courseTestsPath = $this->repository->getCourseTestsPath($course);
        if (Storage::copyDirectory($this->repository->getBaseCourseTestsPath($course), $courseTestsPath)) {
            $this->changeFilesPackage($courseTestsPath, 'courses', $username);
        }

        return $this->getLessonData($user, $course, $lesson);
    }

    public function unsubscribeFromCourse($user, $cid)
    {
        $this->repository->unrelateUserAndCourse($user, $cid);

        // Delete course directory for user
        $course      = $this->courseService->getById($cid);

        Storage::deleteDirectory($this->repository->getCoursePath($course));
        Storage::deleteDirectory($this->repository->getCourseTestsPath($course));

        return [
            'status' => true
        ];
    }

    public function getCurrentLesson($user, $cid)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        $course = $this->courseService->getById($cid);
        $lesson = $user->currentLesson($cid);

        return $this->getLessonData($user, $course, $lesson);
    }

    public function saveLessonProgress($cid, $lid, $data)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        $course = $this->courseService->getById($cid);
        $lesson = $this->lessonService->getById($lid);

        if (!$this->saveLesson($course, $lesson, $data)) {
            throw new SymfonyException\UnprocessableEntityHttpException('Could not save lesson');
        }
    }

    public function nextLesson($user, $cid)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        $lesson = $user->nextLesson($cid);

        if ($lesson === null) {
            throw new LessonNotFoundException();
        }

        $user->courses()->updateExistingPivot($cid, ['lesson_id' => $lesson->id]);

        $course = $this->courseService->getById($cid);
        return $this->getLessonData($user, $course, $lesson);
    }

    public function submitLesson($cid, $lid, $data)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        $course = $this->courseService->getById($cid);
        $lesson = $this->lessonService->getById($lid);

        if (!$this->saveLesson($course, $lesson, $data)) {
            throw new SymfonyException\UnprocessableEntityHttpException('Could not save lesson');
        }

        // Run tests
    }

    protected function saveLesson($course, $lesson, $data)
    {
        $lessonPath = $this->repository->getLessonPath($course, $lesson);

        foreach ($data['files'] as $file) {
            if (Storage::put(joinPaths($lessonPath, $file['name']), $file['content']) === false) {
                return false;
            }
        }

        return true;
    }

    protected function changeFilesPackage($directory, $fromSegment, $toSegment)
    {
        $files = Storage::allFiles($directory);
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

        $files = Storage::allFiles(joinPaths($this->appDirectory, $username, $courseTitle, $lessonTitle));
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
