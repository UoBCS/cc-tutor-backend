<?php

namespace App\Core\CekMachine;

use App\Core\CekMachine\LambdaCalculus\Variable;
use JsonSerializable;

/**
 * Represents an environment in a CEK machine
 */
class Environment implements JsonSerializable
{
    private $bindings;

    /**
     * Creates a new environment
     *
     * @param array $bindings
     */
    public function __construct($bindings = [])
    {
        $this->bindings = $bindings;
    }

    /**
     * Creates a new environment from JSON data
     *
     * @param array $data
     * @return self
     */
    public static function fromJson(array $data) : self
    {
        $environment = new Environment();

        foreach ($data as $binding) {
            $environment->addBinding(Binding::fromJson($binding));
        }

        return $environment;
    }

    /**
     * Adds a binding to the environment
     *
     * @param Binding $binding
     * @return void
     */
    public function addBinding(Binding $binding) : void
    {
        $this->update($binding->getVariable(), $binding->getValue());
    }

    /**
     * Either updates a binding or adds a new one if it does not exist
     *
     * @param Variable $x
     * @param Value $w
     * @return void
     */
    public function update(Variable $x, Value $w) : void
    {
        foreach ($this->bindings as $binding) {
            if ($binding->getVariable()->equals($x)) {
                $binding->setValue($w);
                return;
            }
        }

        $this->bindings[] = new Binding($x, $w);
    }

    /**
     * Searches for the value of a given variable
     *
     * @param Variable $x
     * @return Value
     */
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
        return $this->bindings;
    }

    public function __clone()
    {
        foreach ($this as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $this->$key;
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
