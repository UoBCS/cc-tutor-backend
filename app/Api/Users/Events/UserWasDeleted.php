<?php

namespace App\Api\Users\Events;

use App\Infrastructure\Events\Event;
use App\Api\Users\Models\User;

class UserWasDeleted extends Event
{
    public $resource;
    public $data;

    public function __construct(User $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
