<?php

namespace App\Api\Courses;

use App\Api\Courses\Events\CourseWasCreated;
use App\Api\Courses\Events\CourseWasDeleted;
use App\Api\Courses\Events\CourseWasUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class CourseServiceProvider extends EventServiceProvider
{
    protected $listen = [
        CourseWasCreated::class => [
            // listeners for when a course is created
            Listeners\CommitTransaction::class,
        ],
        CourseWasDeleted::class => [
            // listeners for when a course is deleted
            Listeners\CommitTransaction::class,
        ],
        CourseWasUpdated::class => [
            // listeners for when a course is updated
            Listeners\CommitTransaction::class,
        ]
    ];
}
