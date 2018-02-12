<?php

namespace App\Api\CompilerConstructionAssistant\Controllers;

use App\Api\CompilerConstructionAssistant\Requests\LessonRequest;
use App\Api\CompilerConstructionAssistant\Services\CompilerConstructionAssistantService;
use App\Infrastructure\Http\Controller as BaseController;

class CompilerConstructionAssistantController extends BaseController
{
    private $service;

    public function __construct(CompilerConstructionAssistantService $service)
    {
        $this->service = $service;
    }

    public function getCourses()
    {
        $result = $this->service->getCourses($this->user());

        return $this->response($result);
    }

    public function subscribeToCourse($cid)
    {
        $result = $this->service->subscribeToCourse($this->user(), $cid);

        return $this->response($result);
    }

    public function unsubscribeFromCourse($cid)
    {
        $this->service->unsubscribeFromCourse($this->user(), $cid);

        return $this->response(null, 204);
    }

    public function getCourseLessons($cid)
    {
        $result = $this->service->getCourseLessons($this->user(), $cid);

        return $this->response($result);
    }

    public function getLesson($cid, $lid)
    {
        $result = $this->service->getLesson($this->user(), $cid, $lid);

        return $this->response($result);
    }

    public function getCurrentLesson($cid)
    {
        $result = $this->service->getCurrentLesson($this->user(), $cid);

        return $this->response($result);
    }

    public function saveLessonProgress($cid, $lid, LessonRequest $request)
    {
        $this->service->saveLessonProgress($this->user(), $cid, $lid, $request->all());

        return $this->response(null, 201);
    }

    public function nextLesson($cid)
    {
        $result = $this->service->nextLesson($this->user(), $cid);

        return $this->response($result);
    }

    public function submitLesson($cid, $lid, LessonRequest $request)
    {
        $result = $this->service->submitLesson($this->user(), $cid, $lid, $request->all());

        return $this->response($result);
    }
}
