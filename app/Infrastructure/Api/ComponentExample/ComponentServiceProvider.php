<?php

namespace App\Api\{{ pluralCapitalized }};

use App\Api\{{ pluralCapitalized }}\Events\{{ singularCapitalized }}WasCreated;
use App\Api\{{ pluralCapitalized }}\Events\{{ singularCapitalized }}WasDeleted;
use App\Api\{{ pluralCapitalized }}\Events\{{ singularCapitalized }}WasUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class {{ singularCapitalized }}ServiceProvider extends EventServiceProvider
{
    protected $listen = [
        {{ singularCapitalized }}WasCreated::class => [
            // listeners for when a {{ singular }} is created
            Listeners\CommitTransaction::class,
        ],
        {{ singularCapitalized }}WasDeleted::class => [
            // listeners for when a {{ singular }} is deleted
            Listeners\CommitTransaction::class,
        ],
        {{ singularCapitalized }}WasUpdated::class => [
            // listeners for when a {{ singular }} is updated
            Listeners\CommitTransaction::class,
        ]
    ];
}
