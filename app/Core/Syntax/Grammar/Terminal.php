<?php

namespace App\Core\Syntax\Grammar;

use App\Core\Syntax\Token\TokenType;
use Ds\Hashable;
use JsonSerializable;

class Terminal implements GrammarEntity, Hashable, JsonSerializable
{
    private $tokenType = null;

    public function __construct(TokenType $tokenType = null)
    {
        $this->tokenType = $tokenType;
    }

    public static function epsilon() : Terminal
    {
        return new Terminal();
    }

    public function getTokenType()
    {
        return $this->tokenType;
    }

    public function setTokenType(TokenType $tokenType)
    {
        $this->tokenType = $tokenType;
    }

    public function isEpsilon() : bool
    {
        return $this->tokenType === null;
    }

    public function getName() : string
    {
        return $this->tokenType->name;
    }

    public function isTerminal() : bool
    {
        return true;
    }

    public function isNonTerminal() : bool
    {
        return false;
    }

    public function equals($obj) : bool
    {
        return $obj instanceof Terminal
            && (($this->tokenType === null && $obj->tokenType === null)
                || ($this->tokenType->equals($obj->tokenType)));
    }

    public function hash() : string
    {
        return $this->tokenType === null ? '' : $this->tokenType->hash();
    }

    public function jsonSerialize()
    {
        return $this->tokenType === null ? ['name' => 'ε'] : $this->tokenType;
    }
}
