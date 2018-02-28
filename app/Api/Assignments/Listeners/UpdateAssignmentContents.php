<?php

namespace App\Api\Assignments\Listeners;

use App\Api\Assignments\Repositories\AssignmentRepository;
use Illuminate\Support\Facades\Auth;

class UpdateAssignmentContents
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
        $this->repository->updateContents(Auth::user(), $event->resource, $event->data['extra']);
    }
}
