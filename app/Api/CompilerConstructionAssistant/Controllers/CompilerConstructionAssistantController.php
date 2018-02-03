<?php

namespace App\Api\CompilerConstructionAssistant\Controllers;

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

    public function saveLessonProgress($lid)
    {
        $result = $this->service->saveLessonProgress($lid);

        return $this->response($result);
    }

    public function nextLesson()
    {

    }

    public function submitLesson($cid, $lid)
    {

    }
}
