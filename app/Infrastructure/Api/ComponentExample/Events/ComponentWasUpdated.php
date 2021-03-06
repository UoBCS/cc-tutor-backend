<?php

namespace App\Api\{{ pluralCapitalized }}\Events;

use App\Infrastructure\Events\Event;
use App\Api\{{ pluralCapitalized }}\Models\{{ singularCapitalized }};

class {{ singularCapitalized }}WasUpdated extends Event
{
    public $resource;

    public function __construct({{ singularCapitalized }} $resource, $data = [])
    {
        $this->resource = $resource;
        $this->data     = $data;
    }
}
