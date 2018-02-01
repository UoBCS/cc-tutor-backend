<?php

namespace App\Api\Lessons\Listeners;

use App\Api\Lessons\Repositories\LessonRepository;
use App\Infrastructure\Listeners as CoreListeners;

class CommitTransaction extends CoreListeners\CommitTransaction
{
    public function __construct(LessonRepository $repository)
    {
        $this->repository = $repository;
    }
}
