<?php

namespace App\Core\Syntax\Token;

use App\Core\Syntax\Regex\IRegex;

class TokenType implements IRegex
{
    public $name;
    public $regex;
    public $skippable;
    public $priority;

    public function getRegex()
    {
        return $regex;
    }
}
