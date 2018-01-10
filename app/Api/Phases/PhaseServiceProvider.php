<?php

namespace App\Api\Phases;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class PhaseServiceProvider extends EventServiceProvider
{
    protected $listen = [
        Events\LLRunWasCreated::class => [
            Listeners\CommitTransaction::class,
        ],
        Events\LLRunWasDeleted::class => [
            Listeners\CommitTransaction::class,
        ],
        Events\LLRunWasUpdated::class => [
            Listeners\CommitTransaction::class,
        ]
    ];
}
