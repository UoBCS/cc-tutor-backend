<?php

namespace App\Api\Phases;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Validator;

class PhaseServiceProvider extends EventServiceProvider
{
    protected $listen = [
        Events\NonDetParserRunWasCreated::class => [
            Listeners\CommitTransaction::class,
        ],
        Events\NonDetParserRunWasDeleted::class => [
            Listeners\CommitTransaction::class,
        ],
        Events\NonDetParserRunWasUpdated::class => [
            Listeners\CommitTransaction::class,
        ]
    ];
}
