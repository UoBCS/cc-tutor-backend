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

    public function getTokenType() : TokenType
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
        return $this->tokenType->equals($obj->tokenType);
    }

    public function hash() : string
    {
        return $this->tokenType->hash();
    }

    public function jsonSerialize()
    {
        return $this->tokenType;
    }
}
