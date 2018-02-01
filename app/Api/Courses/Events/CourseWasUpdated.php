<?php

namespace App\Api\Courses\Events;

use App\Infrastructure\Events\Event;
use App\Api\Courses\Models\Course;

class CourseWasUpdated extends Event
{
    public $resource;

    public function __construct(Course $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
