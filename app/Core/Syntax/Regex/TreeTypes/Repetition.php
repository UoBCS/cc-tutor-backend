<?php

namespace App\Core\Syntax\Regex\TreeTypes;

class Repetition extends Regex
{
    private $internal;

    public function __construct(Regex $internal)
    {
        $this->internal = $internal;
    }

    public function getInternal()
    {
        return $this->internal;
    }
}
