<?php

namespace App\Api\Courses\Controllers;

use App\Api\Courses\Services\CourseService;
use App\Infrastructure\Http\Crud\Controller;

class CourseController extends Controller
{
    protected $key = 'course';

    protected $createRules = [
        'course' => 'array|required',
    ];

    protected $updateRules = [
        'course' => 'array|required',
    ];

    public function __construct(CourseService $service)
    {
        $this->service = $service;
    }
}
