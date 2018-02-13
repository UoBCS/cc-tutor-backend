<?php

namespace App\Core\CekMachine;

use LambdaCalculus\Variable;
use JsonSerializable;

class Binding implements JsonSerializble
{
    private $variable;
    private $value;

    public function __construct(Variable $variable, Value $value)
    {
        $this->variable = $variable;
        $this->value = $value;
    }

    public static function fromJson(array $data) : self
    {
        return new Binding(
            Variable::fromJson($data['variable']),
            isset($data['value']['type']) && $data['value']['type'] === 'CONST'
                    ? Constant::fromJson($data['value'])
                    : Closure::fromJson($data['value'])
        );
    }

    public function getVariable() : Variable
    {
        return $this->variable;
    }

    public function setVariable(Variable $value)
    {
        $this->variable = $variable;
    }

    public function getValue() : Value
    {
        return $this->value;
    }

    public function setValue(Value $value)
    {
        $this->value = $value;
    }

    public function jsonSerialize()
    {
        return [
            'variable' => $this->variable,
            'value'    => $this->value
        ];
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
