<?php

namespace App\Api\Phases\Listeners;

use App\Api\Phases\Repositories\LLRunRepository;
use App\Infrastructure\Listeners as CoreListeners;

class CommitTransaction extends CoreListeners\CommitTransaction
{
    public function __construct(LLRunRepository $repository)
    {
        $this->repository = $repository;
    }
}
