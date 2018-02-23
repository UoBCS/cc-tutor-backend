<?php

namespace App\Api\Assignments\Events;

use App\Infrastructure\Events\Event;
use App\Api\Assignments\Models\Assignment;

class AssignmentWasUpdated extends Event
{
    public $resource;

    public function __construct(Assignment $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
