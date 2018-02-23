<?php

namespace App\Api\Assignments\Services;

use App\Api\Assignments\Events;
use App\Api\Assignments\Exceptions;
use App\Api\Assignments\Repositories\AssignmentRepository;
use App\Infrastructure\Http\Crud\Service;

class AssignmentService extends Service
{
    protected $events = [
        'resourceWasCreated' => Events\AssignmentWasCreated::class,
        'resourceWasDeleted' => Events\AssignmentWasDeleted::class,
        'resourceWasUpdated' => Events\AssignmentWasUpdated::class
    ];

    protected $exceptions = [
        'resourceAlreadyExists' => Exceptions\AssignmentAlreadyExistsException::class,
        'resourceNotFound'      => Exceptions\AssignmentNotFoundException::class
    ];

    public function __construct(AssignmentRepository $repository)
    {
        $this->repository = $repository;
    }
}
