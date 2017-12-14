<?php

namespace App\Core\Syntax\Regex;

class PlainRegex implements IRegex
{
    private $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    public function getRegex()
    {
        return $this->regex;
    }
}
