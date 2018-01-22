<?php

namespace App\Api\Phases\Listeners;

use App\Api\Phases\Repositories\NonDetParserRunRepository;
use App\Infrastructure\Listeners as CoreListeners;

class CommitTransaction extends CoreListeners\CommitTransaction
{
    public function __construct(NonDetParserRunRepository $repository)
    {
        $this->repository = $repository;
    }
}
