<?php

namespace App\Api\CompilerConstructionAssistant\Services;

use App\Api\CompilerConstructionAssistant\Exceptions\LessonNotFoundException;
use App\Api\CompilerConstructionAssistant\Exceptions\UserNotSubscribedToCourse;
use App\Api\CompilerConstructionAssistant\Repositories\CompilerConstructionAssistantRepository;
use App\Api\Courses\Services\CourseService;
use App\Api\Lessons\Services\LessonService;
use Illuminate\Support\Facades\File;
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

    public function getCourses($user)
    {
        return [
            'courses'      => $this->courseService->getAll(),
            'user_courses' => $user->courses()->get()
        ];
    }

    public function subscribeToCourse($user, $cid)
    {
        $lessons = $this->lessonService->getBy('course_id', $cid);
        $lesson  = $lessons->where('index', $lessons->min('index'))->first();
        $this->repository->relateUserAndCourse($cid, ['lesson_id' => $lesson->id]);

        // Create course directory for user
        $course      = $this->courseService->getById($cid);
        $username    = normalizeName($user->name);

        // Copy main package
        $coursePath = $this->repository->getCoursePath($course);
        if (File::copyDirectory($this->repository->getBaseCoursePath($course), $coursePath)) {
            $this->changeFilesPackageAndImports($coursePath, 'courses', $username);
        }

        // Copy test package
        $courseTestsPath = $this->repository->getCourseTestsPath($course);
        if (File::copyDirectory($this->repository->getBaseCourseTestsPath($course), $courseTestsPath)) {
            $this->changeFilesPackageAndImports($courseTestsPath, 'courses', $username);
        }

        return $this->repository->getLessonData($course, $lesson);
    }

    public function unsubscribeFromCourse($user, $cid)
    {
        $this->repository->unrelateUserAndCourse($cid);

        // Delete course directory for user
        $course = $this->courseService->getById($cid);

        File::deleteDirectory($this->repository->getCoursePath($course));
        File::deleteDirectory($this->repository->getCourseTestsPath($course));
    }

    public function getCourseLessons($user, $cid)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        return $this->courseService->getById($cid)->lessons()->get();
    }

    public function getLesson($user, $cid, $lid)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        $course = $this->courseService->getById($cid);
        $lesson = $this->lessonService->getById($lid);

        return $this->repository->getLessonData($course, $lesson);
    }

    public function getCurrentLesson($user, $cid)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        $course = $this->courseService->getById($cid);
        $lesson = $user->currentLesson($cid);

        return $this->repository->getLessonData($course, $lesson);
    }

    public function saveLessonProgress($user, $cid, $lid, $data)
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
        return $this->repository->getLessonData($course, $lesson);
    }

    public function submitLesson($user, $cid, $lid, $data)
    {
        if (!$user->isSubscribedTo($cid)) {
            throw new UserNotSubscribedToCourse();
        }

        $course = $this->courseService->getById($cid);
        $lesson = $this->lessonService->getById($lid);

        if (!$this->saveLesson($course, $lesson, $data)) {
            throw new SymfonyException\UnprocessableEntityHttpException('Could not save lesson');
        }

        return $this->runTests($user, $course, $lesson);
    }

    protected function saveLesson($course, $lesson, $data)
    {
        $lessonPath = $this->repository->getLessonPath($course, $lesson);

        foreach ($data['files'] as $file) {
            if (File::put(joinPaths($lessonPath, $file['name']), $file['content']) === false) {
                return false;
            }
        }

        return true;
    }

    protected function changeFilesPackageAndImports($directory, $fromSegment, $toSegment)
    {
        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $content = $file->getContents();

            $content = preg_replace("/(package com\.cctutor\.app\.)(courses)(.*;)/", "$1$toSegment$3", $content);

            $content = preg_replace("/(import com\.cctutor\.app\.)(courses)(.*;)/", "$1$toSegment$3", $content);

            file_put_contents($file->getPathname(), $content);
        }
    }

    protected function runTests($user, $course, $lesson)
    {
        $courseTitle = normalizeName($course->title);
        $lessonTitle = normalizeName($lesson->title);
        $username    = normalizeName($user->name);

        $currentDir = getcwd();

        chdir(storage_path('app/cctutor'));
        [$output, $exitCode] = mvnTest("com.cctutor.app.$username.$courseTitle.$lessonTitle.**");
        chdir($currentDir);

        return [
            'output'    => $output,
            'exit_code' => $exitCode
        ];
    }
}
