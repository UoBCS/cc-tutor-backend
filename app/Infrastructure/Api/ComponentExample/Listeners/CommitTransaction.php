<?php

namespace App\Api\{{ pluralCapitalized }}\Listeners;

use App\Api\{{ pluralCapitalized }}\Repositories\{{ singularCapitalized }}Repository;
use App\Infrastructure\Listeners as CoreListeners;

class CommitTransaction extends CoreListeners\CommitTransaction
{
    public function __construct({{ singularCapitalized }}Repository $repository)
    {
        $this->repository = $repository;
    }
}
