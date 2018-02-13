<?php

namespace App\Core\CekMachine;

use LambdaCalculus\Func;
use JsonSerializable;

class Closure implements JsonSerializble, Value
{
    private $function;
    private $environment;

    public function __construct(Func $function, Environment $environment)
    {
        $this->function = $function;
        $this->environment = $environment;
    }

    public function getFunction() : Func
    {
        return $this->function;
    }

    public function setFunction(Fun $function)
    {
        $this->function = $function;
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function jsonSerialize()
    {
        return null;
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
