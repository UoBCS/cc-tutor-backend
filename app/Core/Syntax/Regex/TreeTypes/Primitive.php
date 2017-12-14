<?php

namespace App\Core\Syntax\Regex\TreeTypes;

class Primitive extends Regex
{
    private $c;

    public function __construct(string $c)
    {
        $this->c = $c;
    }

    public function getChar()
    {
        return $this->c;
    }
}
