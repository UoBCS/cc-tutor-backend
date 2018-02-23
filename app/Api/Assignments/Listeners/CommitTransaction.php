<?php

namespace App\Api\Assignments\Listeners;

use App\Api\Assignments\Repositories\AssignmentRepository;
use App\Infrastructure\Listeners as CoreListeners;

class CommitTransaction extends CoreListeners\CommitTransaction
{
    public function __construct(AssignmentRepository $repository)
    {
        $this->repository = $repository;
    }
}
