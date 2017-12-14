<?php

namespace App\Api\Algorithms\Services;

use App\Core\Automata\FiniteAutomaton;
use App\Core\Syntax\Regex\PlainRegex;

class AlgorithmService
{
    public function regexToNFA($regex)
    {
        $nfa = FiniteAutomaton::fromRegex(new PlainRegex($regex));
        return strval($nfa);
    }
}
