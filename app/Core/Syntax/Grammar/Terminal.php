<?php

namespace App\Core\Syntax\Grammar;

use App\Core\Syntax\Token\TokenType;
use Ds\Hashable;
use JsonSerializable;

class Terminal implements GrammarEntity, Hashable, JsonSerializable
{
    const EPSILON = 'ε';
    private $tokenType = null;

    public function __construct(TokenType $tokenType = null)
    {
        $this->tokenType = $tokenType;
    }

    public static function epsilon() : Terminal
    {
        return new Terminal();
    }

    public static function isEpsilonStruct($data) : bool
    {
        if ($data === null) {
            return true;
        }

        if (is_array($data)) {
            $dCount = count($data);

            return $dCount === 0 ||
                ($dCount === 1 && ($data[0] === null || ($data[0] instanceof Terminal && $data[0]->isEpsilon()) || $data[0] === self::EPSILON));
        }

        if ($data instanceof Terminal) {
            return $data->isEpsilon();
        }

        return false;
    }

    public static function toEpsilon($rhs) : array
    {
        if (!self::isEpsilonStruct($rhs)) {
            return $rhs;
        }

        return [self::epsilon()];
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
        return $this->tokenType === null ? self::EPSILON : $this->tokenType->name;
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
        if (!($obj instanceof Terminal)) {
            return false;
        }

        if ($this->isEpsilon() === $obj->isEpsilon()) {
            return $this->isEpsilon() || $this->tokenType->equals($obj->tokenType);
        }

        return false;
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
