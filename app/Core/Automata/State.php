<?php

namespace App\Core\Automata;

use App\Core\Syntax\Char;
use Ds\Set;
use JsonSerializable;

class State implements JsonSerializable
{
    protected $id;
    protected $data;
    protected $isFinal = false;
    protected $connectedStates = [];

    public function __construct(int $id = null, array $data = [])
    {
        $this->id = $id;
        $this->data = $data;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function isFinal()
    {
        return $this->isFinal;
    }

    public function setFinal(bool $isFinal = true)
    {
        $this->isFinal = $isFinal;
    }

    public function getConnectedStates()
    {
        return $this->connectedStates;
    }

    public function addTransition(self $state, $cs = [])
    {
        if (!is_array($cs)) {
            $cs = [$cs];
        }

        if (count($cs) === 0) {
            $this->_addTransition($state, 'ε');
        }

        foreach ($cs as $c) {
            $this->_addTransition($state, $c);
        }
    }

    public function getState(string $c)
    {
        if ($c === '[ANY]') {
            $set = new Set(array_flatten($this->connectedStates));
            return $set->toArray();
        }

        $outStates = new Set();

        foreach ($this->connectedStates as $char => $states) {
            if ($char === $c) {
                $outStates = $outStates->merge($states);
            }

            if ($char === '[ANY]') {
                $outStates = $outStates->merge($states);
            }

            if (preg_match('/^\[(.)\-(.)\]$/', $char, $matches) === 1) {
                if (preg_match('/^\[(.)\-(.)\]$/', $c, $matchesC) === 1) {
                    if (ord($matchesC[1]) >= ord($matches[1]) && ord($matchesC[2]) <= ord($matches[2])) {
                        $outStates = $outStates->merge($states);
                    }
                }
                else if (ord($c) >= ord($matches[1]) && ord($c) <= ord($matches[2])) {
                    $outStates = $outStates->merge($states);
                }
            }
        }

        return $outStates->toArray(); //isset($this->connectedStates[$c]) ? $this->connectedStates[$c] : [];
    }

    public function jsonSerialize()
    {
        return [
            'id'    => $this->id,
            'data'  => $this->data,
            'final' => $this->isFinal
        ];
    }

    public function __toString()
    {
        return $this->isFinal ? "||$this->id||" : $this->id . '';
    }

    private function _addTransition(self $state, string $c)
    {
        /*for (Char c : cs) {
            Transition transition = new Transition(state, c);
            int i = transitions.indexOf(transition);
            if (i == -1) { // !transitions.contains(transition)
                transitions.add(transition);
            } else if (c.any || c instanceof  RangeChar) {
                transitions.get(i).setChar(c); // Update for inclusiveness
            }
        }*/

        $states = new Set(isset($this->connectedStates[$c]) ? $this->connectedStates[$c] : []);
        $states->add($state);
        $this->connectedStates[$c] = $states->toArray();
    }
}
