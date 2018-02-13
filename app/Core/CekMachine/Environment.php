<?php

namespace App\Core\CekMachine;

use LambdaCalculus\Variable;
use JsonSerializable;

class Environment implements JsonSerializble
{
    private $bindings;

    public function __construct($bindings = [])
    {
        $this->bindings = $bindings;
    }

    public static function fromJson(array $data) : self
    {
        $environment = new Environment();

        foreach ($data as $binding) {
            $environment->addBinding(Binding::fromJson($binding));
        }

        return $environment;
    }

    public function addBinding(Binding $binding)
    {
        $this->update($binding->getVariable(), $binding->getValue());
    }

    public function update(Variable $x, Value $w)
    {
        foreach ($this->bindings as $binding) {
            if ($binding->getVariable()->equals($x)) {
                $binding->setValue($w);
                return;
            }
        }

        $this->bindings[] = new Binding($x, $w);
    }

    public function lookup(Variable $x)
    {
        foreach ($this->bindings as $binding) {
            if ($binding->getVariable()->equals($x)) {
                return $binding->getValue();
            }
        }

        return null;
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
