<?php

namespace App\Core\CekMachine;

use App\Infrastructure\Utils\Ds\Pair;
use JsonSerializable;

class Frame implements JsonSerializble
{
    private $content;

    public function __construct(Pair $content)
    {
        $this->content = $content;
    }

    public function getContent() : Pair
    {
        return $this->content;
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
