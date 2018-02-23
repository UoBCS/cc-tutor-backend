<?php

namespace App\Api\Assignments;

use App\Api\Assignments\Events\AssignmentWasCreated;
use App\Api\Assignments\Events\AssignmentWasDeleted;
use App\Api\Assignments\Events\AssignmentWasUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class AssignmentServiceProvider extends EventServiceProvider
{
    protected $listen = [
        AssignmentWasCreated::class => [
            // listeners for when a assignment is created
            Listeners\CommitTransaction::class,
        ],
        AssignmentWasDeleted::class => [
            // listeners for when a assignment is deleted
            Listeners\CommitTransaction::class,
        ],
        AssignmentWasUpdated::class => [
            // listeners for when a assignment is updated
            Listeners\CommitTransaction::class,
        ]
    ];
}
