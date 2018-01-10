<?php

namespace App\Core\Syntax\Token;

use App\Core\Syntax\Regex\IRegex;
use Ds\Hashable;
use Ds\Set;
use JsonSerializable;

class TokenType implements IRegex, JsonSerializable, Hashable
{
    public $name;
    public $regex;
    public $skippable;
    public $priority;

    public static function fromDataArray(array $data) : Set
    {
        $tokenTypes = new Set();

        foreach ($data as $tokenType) {
            $tt = new TokenType();
            $tt->name = $tokenType['name'];
            $tt->regex = $tokenType['regex'];
            $tt->skippable = $tokenType['skippable'];
            $tt->priority = $tokenType['priority'];

            $tokenTypes->add($tt);
        }

        return $tokenTypes;
    }

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
        return $this->regex;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'regex' => $this->regex,
            'skippable' => $this->skippable,
            'priority' => $this->priority
        ];
    }

    public function equals($obj)
    {
        return $this->name === $obj->name
            && $this->regex === $obj->regex
            && $this->skippable === $obj->skippable
            && $this->priority === $obj->priority;
    }

    public function hash()
    {
        return $this->name . '|' . $this->regex . '|' . $this->skippable . '|' . $this->priority;
    }

    public function __toString()
    {
        return $this->name;
    }
}
