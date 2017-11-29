<?php

namespace App\Api\Users;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class UserServiceProvider extends EventServiceProvider
{
    protected $listen = [
        Events\UserWasCreated::class => [
            // listeners for when a user is created
            Listeners\CommitTransaction::class,
        ],
        Events\UserWasDeleted::class => [
            // listeners for when a user is deleted
            Listeners\CommitTransaction::class,
        ],
        Events\UserWasUpdated::class => [
            // listeners for when a user is updated
            Listeners\CommitTransaction::class,
        ]
    ];
}
