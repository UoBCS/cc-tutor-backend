<?php

namespace App\Core\Automata;

use Ds\Set;
use JsonSerializable;

/**
 * Represents a state in a deterministic finite automaton
 */
class DfaState extends State implements JsonSerializable
{
    public $serialization = [
        'showStates' => true
    ];
    private $states;

    public function __construct(Set $states)
    {
        $this->states = $states;
    }

    public function getStates()
    {
        return $this->states;
    }

    public function jsonSerialize()
    {
        return $this->serialization['showStates']
                ? array_merge(parent::jsonSerialize(), ['states' => $this->states])
                : parent::jsonSerialize();
    }
}
