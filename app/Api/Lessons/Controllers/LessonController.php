<?php

namespace App\Api\Lessons\Controllers;

use App\Api\Lessons\Services\LessonService;
use App\Infrastructure\Http\Crud\Controller;

class LessonController extends Controller
{
    protected $key = 'lesson';

    protected $createRules = [
        'lesson' => 'array|required',
    ];

    protected $updateRules = [
        'lesson' => 'array|required',
    ];

    public function __construct(LessonService $service)
    {
        $this->service = $service;
    }
}
