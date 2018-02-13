<?php

namespace App\Core\CekMachine\LambdaCalculus;
use JsonSerializable;

class Func extends Expression implements JsonSerializble
{
    private $name;
    private $body;

    public function __construct(Variable $name, Expression $body)
    {
        $this->name = $name;
        $this->body = $body;
    }

    public function reducible() : bool
    {
        return $this->body->reducible();
    }

    public function reduce() : Expression
    {
        if ($this->body->reducible()) {
            return new Func($this->name, $this->body->reduce());
        }

        return $this;
    }

    public function deepReduce() : Expression
    {
        return new Func($this->name, $this->body->deepReduce());
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getBody() : Expression
    {
        return $this->body;
    }

    public function setBody(Expression $body)
    {
        $this->body = $body;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'FUNC',
            'name' => $this->name,
            'body' => $this->body
        ];
    }

    public function __toString()
    {
        return 'λ' . $this->name . '.' . $this->body;
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
