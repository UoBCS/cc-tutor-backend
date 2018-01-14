<?php

namespace App\Api\Phases;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Validator;

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

    public function boot()
    {
        parent::boot();

        Validator::extend('token_types', function ($attribute, $value, $parameters, $validator) {
            if (!is_array($value)) {
                return false;
            }

            foreach ($value as $v) {
                if (!isset($v['name']) || !isset($v['regex']) || !isset($v['skippable']) || !isset($v['priority'])) {
                    return false;
                }
            }

            return true;
        });

        Validator::extend('grammar', function ($attribute, $value, $parameters, $validator) {
            if (!is_array($value)) {
                return false;
            }

            if (!isset($value['productions']) || !isset($value['start_symbol'])) {
                return false;
            }

            return true;
        });
    }
}
