<?php

namespace App\Api\Courses\Events;

use App\Infrastructure\Events\Event;
use App\Api\Courses\Models\Course;

class CourseWasCreated extends Event
{
    public $resource;
    public $data;

    public function __construct(Course $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
