<?php

namespace App\Core\CekMachine;

use App\Core\CekMachine\LambdaCalculus\Variable;
use JsonSerializable;

/**
 * Represents a binding in an environment
 */
class Binding implements JsonSerializable
{
    private $variable;
    private $value;

    /**
     * Creates a new binding
     *
     * @param Variable $variable
     * @param Value $value
     */
    public function __construct(Variable $variable, Value $value)
    {
        $this->variable = $variable;
        $this->value = $value;
    }

    /**
     * Creates a new binding from JSON data
     *
     * @param array $data
     * @return self
     */
    public static function fromJson(array $data) : self
    {
        return new Binding(
            Variable::fromJson($data['variable']),
            $data['value']['type'] === 'CONST'
                ? Constant::fromJson($data['value'])
                : Closure::fromJson($data['value'])
        );
    }

    /**
     * Gets the variable associated with the binding
     *
     * @return Variable
     */
    public function getVariable() : Variable
    {
        return $this->variable;
    }

    /**
     * Sets the variable associated with the binding
     *
     * @param Variable $value
     * @return void
     */
    public function setVariable(Variable $value) : void
    {
        $this->variable = $variable;
    }

    /**
     * Gets the value associated with the binding
     *
     * @return Value
     */
    public function getValue() : Value
    {
        return $this->value;
    }

    /**
     * Sets the value associated with the binding
     *
     * @param Value $value
     * @return void
     */
    public function setValue(Value $value) : void
    {
        $this->value = $value;
    }

    public function jsonSerialize()
    {
        return [
            'variable' => $this->variable->jsonSerialize(),
            'value'    => $this->value->jsonSerialize()
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
