<?php

namespace App\Api\Phases\Events;

use App\Infrastructure\Events\Event;
use App\Api\Phases\Models\LLRun;

class LLRunWasCreated extends Event
{
    public $resource;
    public $data;

    public function __construct(LLRun $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
