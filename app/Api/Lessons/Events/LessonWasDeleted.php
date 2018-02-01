<?php

namespace App\Api\Lessons\Events;

use App\Infrastructure\Events\Event;
use App\Api\Lessons\Models\Lesson;

class LessonWasDeleted extends Event
{
    public $resource;
    public $data;

    public function __construct(Lesson $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
