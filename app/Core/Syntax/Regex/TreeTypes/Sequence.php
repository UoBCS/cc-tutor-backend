<?php

namespace App\Core\Syntax\Regex\TreeTypes;

class Sequence extends Regex
{
    private $first;
    private $second;

    public function __construct(Regex $first, Regex $second)
    {
        $this->first = $first;
        $this->second = $second;
    }

    public function getFirst()
    {
        return $this->first;
    }

    public function getSecond()
    {
        return $this->second;
    }
}
