<?php

namespace App\Core\CekMachine\LambdaCalculus;
use Ds\Hashable;
use JsonSerializable;

class Variable extends Expression implements Hashable, JsonSerializble
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function reducible()
    {
        return false;
    }

    public function reduce()
    {
        return this;
    }

    public function deepReduce()
    {
        return this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function equals($obj) : bool
    {
        if ($this === $obj) {
            return true;
        }

        if (!($obj instanceof self)) {
            return false;
        }

        return $this->name === $obj->name;
    }

    public function hash()
    {
        return $this->name;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'VAR',
            'name' => $this->name
        ];
    }

    public function __toString()
    {
        return $this->name;
    }

    public function __clone()
    {
        foreach ($this as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $this->key;
            } else if (is_array($value)) {
                $newArray = [];
                foreach ($value as $arrayKey => $arrayValue) {
                    $newArray[$arrayKey] = is_object($arrayValue) ? clone $arrayValue : $arrayValue;
                }
                $this->$key = $newArray;
            }
        }
    }
}
