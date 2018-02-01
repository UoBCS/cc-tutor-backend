<?php

namespace App\Api\Courses\Services;

use App\Api\Courses\Events;
use App\Api\Courses\Exceptions;
use App\Api\Courses\Repositories\CourseRepository;
use App\Infrastructure\Http\Crud\Service;

class CourseService extends Service
{
    protected $events = [
        'resourceWasCreated' => Events\CourseWasCreated::class,
        'resourceWasDeleted' => Events\CourseWasDeleted::class,
        'resourceWasUpdated' => Events\CourseWasUpdated::class
    ];

    protected $exceptions = [
        'resourceAlreadyExists' => Exceptions\CourseAlreadyExistsException::class,
        'resourceNotFound'      => Exceptions\CourseNotFoundException::class
    ];

    public function __construct(CourseRepository $repository)
    {
        $this->repository = $repository;
    }
}
