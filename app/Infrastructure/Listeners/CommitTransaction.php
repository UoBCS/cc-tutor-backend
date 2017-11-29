<?php

namespace App\Infrastructure\Listeners;

use App\Infrastructure\Database\Eloquent\Repository;

/**
 * Base listener to commit a transaction
 */
abstract class CommitTransaction
{
    /**
     * The repository to call commit on
     *
     * @var \App\Infrastructure\Database\Eloquent
     */
    protected $repository;

    /**
     * Handle the event
     *
     * @param  \App\Infrastructure\Events\Event $event
     * @return void
     */
    public function handle($event)
    {
        $this->repository->commitTransaction();
    }
}
