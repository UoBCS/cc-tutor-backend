<?php

namespace App\Api\Phases\Events;

use App\Infrastructure\Events\Event;
use App\Api\Phases\Models\NonDetParserRun;

class NonDetParserRunWasDeleted extends Event
{
    public $resource;
    public $data;

    public function __construct(NonDetParserRun $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
