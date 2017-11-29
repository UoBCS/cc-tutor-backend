<?php

namespace App\Api\Users\Listeners;

use App\Api\Users\Repositories\UserRepository;
use App\Infrastructure\Listeners as CoreListeners;

class CommitTransaction extends CoreListeners\CommitTransaction
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }
}
