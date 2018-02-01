<?php

namespace App\Api\CompilerConstructionAssistant\Controllers;

use App\Api\CompilerConstructionAssistant\Services\CompilerConstructionAssistantService;
use App\Infrastructure\Http\Controller as BaseController;

class CompilerConstructionAssistantController extends Controller
{
    private $service;

    public function __construct(CompilerConstructionAssistantService $service)
    {
        $this->service = $service;
    }

    public function subscribeToCourse($cid)
    {
        $this->service->subscribeToCourse($this->user(), $cid);

        return $this->response([
            'status'  => true,
            'message' => 'Successfully subscribed to course.'
        ]);
    }
}
