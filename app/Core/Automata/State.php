<?php

namespace App\Core\Automata;

use App\Core\Syntax\Char;
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

    public function setData(array $data)
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

    public function addTransition(self $state, array $cs = [])
    {
        if (count($cs) === 0) {
            $this->_addTransition($state, 'ε');
        }

        foreach ($cs as $c) {
            $this->_addTransition($state, $c);
        }
    }

    public function getState(string $c)
    {
        return $this->connectedStates[$c];
    }

    public function jsonSerialize()
    {
        return $this->isFinal ? "||$this->id||" : $this->id . '';
    }

    public function __toString()
    {
        return $this->isFinal ? "||$this->id||" : $this->id . '';
    }

    private function _addTransition(self $state, string $c)
    {
        $states = isset($this->connectedStates[$c]) ? $this->connectedStates[$c] : [];
        if (!in_array($state, $states) || $c === '[ANY]' || preg_match($c, '^\[.-.\]$') === 1) {
            $states[] = $state;
            $this->connectedStates[$c] = $states;
        }
    }
}
