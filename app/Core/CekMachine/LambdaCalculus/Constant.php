<?php

namespace App\Core\CekMachine\LambdaCalculus;

use App\Core\CekMachine\Value;
use Ds\Hashable;
use JsonSerializable;

class Constant extends Expression implements Hashable, JsonSerializble, Value
{
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
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

    public function getValue() : int
    {
        return $this->value;
    }

    public function setValue(int $value)
    {
        $this->value = $value;
    }

    public function equals($obj) : bool
    {
        if ($this === $obj) {
            return true;
        }

        if (!($obj instanceof self)) {
            return false;
        }

        return $this->value === $obj->value;
    }

    public function hash()
    {
        return $this->value + '';
    }

    public function jsonSerialize()
    {
        return [
            'type'  => 'CONST',
            'value' => $this->value
        ];
    }

    public function __toString()
    {
        return $this->value;
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
