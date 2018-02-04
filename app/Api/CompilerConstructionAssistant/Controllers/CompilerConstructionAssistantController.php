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

    public function subscribeToCourse($cid)
    {
        $result = $this->service->subscribeToCourse($this->user(), $cid);

        return $this->response($result);
    }

    public function unsubscribeFromCourse($cid)
    {
        $result = $this->service->unsubscribeFromCourse($this->user(), $cid);

        return $this->response($result);
    }

    public function getCurrentLesson($cid)
    {
        $result = $this->service->getCurrentLesson($this->user(), $cid);

        return $this->response($result);
    }

    public function saveLessonProgress($cid, $lid, LessonRequest $request)
    {
        $this->service->saveLessonProgress($cid, $lid, $request->all());

        return $this->response(null, 201);
    }

    public function nextLesson($cid)
    {
        $result = $this->service->nextLesson($this->user(), $cid);

        return $this->response($result);
    }

    public function submitLesson($cid, $lid, LessonRequest $request)
    {
        $this->service->submitLesson($cid, $lid, $request->all());
    }
}
