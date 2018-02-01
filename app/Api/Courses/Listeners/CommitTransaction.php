<?php

namespace App\Api\Courses\Listeners;

use App\Api\Courses\Repositories\CourseRepository;
use App\Infrastructure\Listeners as CoreListeners;

class CommitTransaction extends CoreListeners\CommitTransaction
{
    public function __construct(CourseRepository $repository)
    {
        $this->repository = $repository;
    }
}
