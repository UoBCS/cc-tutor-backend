<?php

namespace App\Core\Syntax\Grammar;

class NonTerminal implements GrammarEntity
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function isTerminal() : bool
    {
        return false;
    }

    public function isNonTerminal() : bool
    {
        return true;
    }

    public function equals($obj) : bool
    {
        return $obj instanceof NonTerminal && $this->getName() === $obj->getName();
    }

    public function hash()
    {
        return $this->name;
    }

    public function jsonSerialize()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function __clone() {
        return new NonTerminal($this->name);
	}
}
