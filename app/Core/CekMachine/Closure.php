<?php

namespace App\Core\CekMachine;

use App\Core\CekMachine\LambdaCalculus\Func;
use JsonSerializable;

/**
 * Represents a closure
 */
class Closure implements JsonSerializable, Value
{
    private $function;
    private $environment;

    /**
     * Creates a new closure
     *
     * @param Func $function
     * @param Environment $environment
     */
    public function __construct(Func $function, Environment $environment)
    {
        $this->function = $function;
        $this->environment = $environment;
    }

    /**
     * Creates a new closure from JSON data
     *
     * @param array $data
     * @return self
     */
    public static function fromJson(array $data) : self
    {
        return new Closure(Func::fromData($data['function']), Environment::fromData($data['environment']));
    }

    /**
     * Gets the function
     *
     * @return Func
     */
    public function getFunction() : Func
    {
        return $this->function;
    }

    /**
     * Sets the function
     *
     * @param Fun $function
     * @return void
     */
    public function setFunction(Fun $function) : void
    {
        $this->function = $function;
    }

    /**
     * Gets the environment associated with the closure
     *
     * @return Environment
     */
    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    /**
     * Sets the environment associated with the closure
     *
     * @param Environment $environment
     * @return void
     */
    public function setEnvironment(Environment $environment) : void
    {
        $this->environment = $environment;
    }

    public function jsonSerialize()
    {
        return [
            'type'        => 'CLOSURE',
            'function'    => $this->function,
            'environment' => $this->environment
        ];
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
