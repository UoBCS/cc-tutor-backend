<?php

namespace App\Core\Automata;

use Ds\Set;
use JsonSerializable;

class DfaState extends State implements JsonSerializable
{
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
        return array_merge(parent::jsonSerialize(), ['states' => $this->states]);
    }
}
