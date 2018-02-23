<?php

namespace App\Api\Assignments\Events;

use App\Infrastructure\Events\Event;
use App\Api\Assignments\Models\Assignment;

class AssignmentWasCreated extends Event
{
    public $resource;
    public $data;

    public function __construct(Assignment $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
