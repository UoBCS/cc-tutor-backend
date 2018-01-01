<?php

namespace App\Api\Algorithms\Services;

use App\Core\Automata\FiniteAutomaton;
use App\Core\Inspector;
use App\Core\Syntax\Regex\PlainRegex;

class AlgorithmService
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
    }

    public function regexToNfa($regex)
    {
        $result = FiniteAutomaton::fromRegex(new PlainRegex($regex));
        return [
            'breakpoints' => $this->inspector->getState('breakpoints'),
            'nfa'         => $result['nfa'],
            'regex_tree'  => $result['regex_tree'],
        ];
    }

    public function nfaToDfa($nfa)
    {
        $result = FiniteAutomaton::fromArray($nfa)->toDfa();
        return [
            'breakpoints' => $this->inspector->getState('breakpoints'),
            'dfa'         => $result
        ];
    }
}
