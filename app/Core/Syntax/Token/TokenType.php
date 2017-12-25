<?php

namespace App\Core\Syntax\Token;

use App\Core\Syntax\Regex\IRegex;

class TokenType implements IRegex
{
    public $name;
    public $regex;
    public $skippable;
    public $priority;

    public static function ws()
    {
        $tt = new TokenType();
        $tt->name = 'WS';
        $tt->regex = '';
        $tt->skippable = true;
        $tt->priority = 0;

        return $tt;
    }

    public function getRegex()
    {
        return $regex;
    }

    public function __toString()
    {
        return $this->name;
    }
}
