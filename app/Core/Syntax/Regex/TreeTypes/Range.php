<?php

namespace App\Core\Syntax\Regex\TreeTypes;

class Range extends Regex
{
    private $a;
    private $b;

    public function __construct(string $a, string $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }
}
