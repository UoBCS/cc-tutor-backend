<?php

namespace App\Core\CekMachine;

use App\Core\CekMachine\LambdaCalculus\Expression;
use App\Infrastructure\Utils\Ds\Pair;
use JsonSerializable;

class Frame implements JsonSerializable
{
    private $content;

    public function __construct(Pair $content)
    {
        $this->content = $content;
    }

    public static function fromJson(array $data) : self
    {
        if ($data[0] === null) {
            $pair = new Pair($data[0], new Pair(
                Expression::fromJson(isset($data[1]['expression']) ? $data[1]['expression'] : $data[1][0]),
                Environment::fromJson(isset($data[1]['environment']) ? $data[1]['environment'] : $data[1][1])
            ));

            return new Frame($pair);
        }

        $pair = new Pair(
            $data[1]['type'] === 'CONST' ? Expression::fromJson($data[1]) : Closure::fromJson($data[1]),
            null
        );

        return new Frame($pair);
    }

    public function getContent() : Pair
    {
        return $this->content;
    }

    public function jsonSerialize()
    {
        return $this->content->jsonSerialize();
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
