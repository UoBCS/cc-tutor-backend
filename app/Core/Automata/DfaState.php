<?php

namespace App\Core\Automata;

use Ds\Set;

class DfaState extends State
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
}
