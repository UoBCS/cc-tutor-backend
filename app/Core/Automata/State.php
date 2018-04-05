<?php

namespace App\Core\Automata;

use App\Core\Syntax\Char;
use Ds\Set;
use JsonSerializable;

/**
 * Represents a state in a finite automaton
 */
class State implements JsonSerializable
{
    protected $id;
    protected $data;
    protected $isFinal = false;
    protected $isError = false;
    protected $connectedStates = [];
    protected $jsonSerializeOptions = [
        'showData' => true
    ];

    /**
     * Creates a new state
     *
     * @param integer $id The ID of the state
     * @param array $data The data associated with the state
     */
    public function __construct(int $id = null, array $data = [])
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Returns an error state
     *
     * @return self
     */
    public static function error() : self
    {
        $state = new State();
        $state->id = -1;
        $state->isError = true;
        return $state;
    }

    /**
     * Returns the ID
     *
     * @return integer
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Sets the ID
     *
     * @param integer $id
     * @return void
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    /**
     * Returns the data associated with the state
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data associated with the state
     *
     * @param mixed $data
     * @return void
     */
    public function setData($data) : void
    {
        $this->data = $data;
    }

    /**
     * Checks if the state is final
     *
     * @return boolean
     */
    public function isFinal() : bool
    {
        return $this->isFinal;
    }

    /**
     * Sets the state type
     *
     * @param boolean $isFinal
     * @return void
     */
    public function setFinal(bool $isFinal = true) : void
    {
        $this->isFinal = $isFinal;
    }

    /**
     * Checks whether the state is an error one
     *
     * @return boolean
     */
    public function isError() : bool
    {
        return $this->isError;
    }

    /**
     * Gets the connected states
     *
     * @param mixed $c The transition character
     * @return mixed
     */
    public function getConnectedStates($c = null)
    {
        return $c === null ? $this->connectedStates : $this->connectedStates[$c];
    }

    /**
     * Checks if there is a transition from the state
     *
     * @param mixed $c
     * @return boolean
     */
    public function hasTransition($c) : bool
    {
        return array_key_exists($c, $this->connectedStates);
    }

    /**
     * Adds transitions to the state
     *
     * @param self $state
     * @param array $cs
     * @return void
     */
    public function addTransition(self $state, array $cs = []) : void
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

    /**
     * Removes all transitions pointing to a specified state
     *
     * @param self $state
     * @return void
     */
    public function removeAllTransitions(self $state) : void
    {
        foreach ($this->getChars() as $char) {
            $this->removeTransition($state, $char);
        }
    }

    /**
     * Removes a specific transition pointing to a specified state
     *
     * @param self $state
     * @param mixed $c
     * @return void
     */
    public function removeTransition(self $state, $c) : void
    {
        if (!isset($this->connectedStates[$c])) {
            return;
        }

        $states = $this->connectedStates[$c];
        arrayRemove($states, $state, false, true);
        $this->connectedStates[$c] = $states;
    }

    /**
     * Resets the connected states
     *
     * @return void
     */
    public function resetTransitions() : void
    {
        $this->connectedStates = [];
    }

    /**
     * Gets the transitions' characters from this state
     *
     * @return array
     */
    public function getChars() : array
    {
        $charSet = new Set(array_keys($this->connectedStates));
        return $charSet->toArray();
    }

    /**
     * Gets states from the transition character
     *
     * @param string $c
     * @return array
     */
    public function getState(string $c) : array
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

        return $outStates->toArray();
    }

    /**
     * Sets the options for the JSON serialization
     *
     * @param array $jsonSerializeOptions
     * @return void
     */
    public function setJsonSerializeOptions(array $jsonSerializeOptions) : void
    {
        $this->jsonSerializeOptions = $jsonSerializeOptions;
    }

    public function jsonSerialize()
    {
        $state = [
            'id'    => $this->id,
            'final' => $this->isFinal
        ];

        if ($this->jsonSerializeOptions['showData']) {
            $state['data'] = $this->data;
        }

        return $state;
    }

    public function __toString()
    {
        return $this->isFinal ? "||$this->id||" : $this->id . '';
    }

    private function _addTransition(self $state, string $c)
    {
        $states = new Set(isset($this->connectedStates[$c]) ? $this->connectedStates[$c] : []);
        $states->add($state);
        $this->connectedStates[$c] = $states->toArray();
    }
}
