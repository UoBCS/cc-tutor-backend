<?php

namespace App\Api\Assignments;

use App\Api\Assignments\Events\AssignmentWasCreated;
use App\Api\Assignments\Events\AssignmentWasDeleted;
use App\Api\Assignments\Events\AssignmentWasUpdated;
use App\Api\Assignments\Events\AssignmentWillBeDeleted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class AssignmentServiceProvider extends EventServiceProvider
{
    protected $listen = [
        AssignmentWillBeDeleted::class => [
            // listeners for when an assignment will be deleted
            Listeners\DeleteDirectories::class,
        ],
        AssignmentWasCreated::class => [
            // listeners for when an assignment is created
            Listeners\CommitTransaction::class,
        ],
        AssignmentWasDeleted::class => [
            // listeners for when an assignment is deleted
            Listeners\CommitTransaction::class,
        ],
        AssignmentWasUpdated::class => [
            // listeners for when an assignment is updated
            Listeners\CommitTransaction::class,
        ]
    ];
}
