<?php

namespace App\Api\Lessons;

use App\Api\Lessons\Events\LessonWasCreated;
use App\Api\Lessons\Events\LessonWasDeleted;
use App\Api\Lessons\Events\LessonWasUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class LessonServiceProvider extends EventServiceProvider
{
    protected $listen = [
        LessonWasCreated::class => [
            // listeners for when a lesson is created
            Listeners\CommitTransaction::class,
        ],
        LessonWasDeleted::class => [
            // listeners for when a lesson is deleted
            Listeners\CommitTransaction::class,
        ],
        LessonWasUpdated::class => [
            // listeners for when a lesson is updated
            Listeners\CommitTransaction::class,
        ]
    ];
}
