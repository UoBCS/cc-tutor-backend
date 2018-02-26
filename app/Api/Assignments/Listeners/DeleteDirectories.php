<?php

namespace App\Api\Assignments\Listeners;

use App\Api\Assignments\Repositories\AssignmentRepository;

class DeleteDirectories
{
    private $repository;

    public function __construct(AssignmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the event
     *
     * @param  \App\Infrastructure\Events\Event $event
     * @return void
     */
    public function handle($event)
    {
        $this->repository->deleteTeacherDirectories($event->resource);
        $this->repository->deleteStudentsDirectories($event->resource);
    }
}
