<?php

namespace App\Api\Algorithms\Services;

use App\Core\Automata\FiniteAutomaton;
use App\Core\CekMachine\CekMachine;
use App\Core\Inspector;
use App\Core\Syntax\Regex\PlainRegex;

class AlgorithmService
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
    }

    public function regexToNfa(string $regex) : array
    {
        $result = FiniteAutomaton::fromRegex(new PlainRegex($regex));
        return [
            'breakpoints' => $this->inspector->getState('breakpoints'),
            'nfa'         => $result['nfa'],
            'regex_tree'  => $result['regex_tree'],
        ];
    }

    public function nfaToDfa(array $nfa) : array
    {
        $result = FiniteAutomaton::fromArray($nfa)->toDfa();
        return [
            'breakpoints' => $this->inspector->getState('breakpoints'),
            'dfa'         => $result
        ];
    }

    public function minimizeDfa(array $dfa) : array
    {
        $result = FiniteAutomaton::fromArray($dfa)->minimizeDfa();
        return [
            'dfa' => $result
        ];
    }

    public function cekMachineNextStep(array $initialMachineState) : array
    {
        $machine = CekMachine::fromJson($initialMachineState);
        $machine->nextStep();

        return $machine->jsonSerialize();
    }

    public function cekMachineRun(array $initialMachineState) : array
    {
        $machine = CekMachine::fromJson($initialMachineState);

        return $machine->run();
    }
}
