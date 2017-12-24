<?php

namespace App\Core\Syntax\Regex\TreeTypes;

use JsonSerializable;

abstract class Regex implements JsonSerializable
{
    public function jsonSerialize()
    {
        if ($this instanceof Choice) {
            return [
                'name' => 'OR',
                'children' => [
                    $this->getLeft(),
                    $this->getRight()
                ]
            ];
        } else if ($this instanceof Repetition) {
            return [
                'name' => 'REP',
                'children' => [
                    $this->getInternal()
                ]
            ];
        } else if ($this instanceof RepetitionFromOne) {
            return [
                'name' => 'SEQ',
                'children' => [
                    $this->getInternal(),
                    [
                        'name' => 'REP',
                        'children' => $this->getInternal()
                    ]
                ]
            ];
        } else if ($this instanceof Optional) {
            return [
                'name' => 'OR',
                'children' => [
                    $this->getInternal(),
                    ['name' => 'ε']
                ]
            ];
        } else if ($this instanceof Sequence) {
            return [
                'name' => 'SEQ',
                'children' => [
                    $this->getFirst(),
                    $this->getSecond()
                ]
            ];
        } else if ($this instanceof GroupItems) {
            /*$items = $this->getItems();
            $is = [];

            for ($i = 0; $i < count($items); $i++) {
                $is[] = $items[$i];
            }

            return $faBuilder;*/

            return [
                'name' => 'OR',
                'children' => $this->getItems()
            ];
        } else if ($this instanceof Range) {
            $a = $this->getA();
            $b = $this->getB();

            if (ord($a) > ord($b)) {
                throw new Exception('Regular expression contains an invalid range.');
            }

            return ['name' => "[$a-$b]"];
        } else if ($this instanceof Primitive) {
            return ['name' => $this->getChar()];
        } else if ($this instanceof AnyChar) {
            return ['name' => '[ANY]'];
        } else {
            return ['name' => 'ε'];
        }
    }
}
