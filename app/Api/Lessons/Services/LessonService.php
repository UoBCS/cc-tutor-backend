<?php

namespace App\Api\Lessons\Services;

use App\Api\Lessons\Events;
use App\Api\Lessons\Exceptions;
use App\Api\Lessons\Repositories\LessonRepository;
use App\Infrastructure\Http\Crud\Service;

class LessonService extends Service
{
    protected $events = [
        'resourceWasCreated' => Events\LessonWasCreated::class,
        'resourceWasDeleted' => Events\LessonWasDeleted::class,
        'resourceWasUpdated' => Events\LessonWasUpdated::class
    ];

    protected $exceptions = [
        'resourceAlreadyExists' => Exceptions\LessonAlreadyExistsException::class,
        'resourceNotFound'      => Exceptions\LessonNotFoundException::class
    ];

    public function __construct(LessonRepository $repository)
    {
        $this->repository = $repository;
    }
}
