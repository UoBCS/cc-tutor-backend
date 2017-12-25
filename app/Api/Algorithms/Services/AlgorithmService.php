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
        $this->inspector = resolve('App\Core\Inspector');
    }

    public function regexToNfa($regex)
    {
        $result = FiniteAutomaton::fromRegex(new PlainRegex($regex));
        return [
            'regex_tree'              => $result['regex_tree'],
            'regex_tree_to_nfa_steps' => $this->inspector->getState()['actions'],
            'nfa'                     => $result['nfa']
        ];
    }

    public function nfaToDfa($nfa)
    {
        $result = FiniteAutomaton::fromArray($nfa)->toDfa();
        return $result;
    }
}
