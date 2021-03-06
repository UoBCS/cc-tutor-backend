<?php

namespace App\Core\Automata;

use Ds\Map;
use Ds\Set;
use Ds\Stack;

/**
 * Helper class for converting an NFA to a DFA
 */
class DfaConverter
{
    private static $inspector;

    public static function init()
    {
        self::$inspector = inspector();
        self::$inspector->createStore('breakpoints', 'array');
    }

    /**
     * Converts an NFA to a DFA give the initial state in the NFA
     *
     * @param State $initial
     * @return FiniteAutomaton
     */
    public static function toDfa(State $initial) : FiniteAutomaton
    {
        /* > */ self::$inspector->breakpoint('highlight_initial_nfa_state', [ 'state' => $initial ]);

        $epsilonReachableStates = new Set();

        self::epsilonClosure($epsilonReachableStates, $initial);
        /* > */ self::$inspector->breakpoint('initial_state_epsilon_closure', [
        /* > */    'initial'          => $initial,
        /* > */    'reachable_states' => $epsilonReachableStates
        /* > */ ]);

        $initialDfaState = new DfaState($epsilonReachableStates);
        /* > */ self::$inspector->breakpoint('initial_dfa_state', $initialDfaState);

        $dStates = new Map();

        $dStates->put($initialDfaState, false);

        while (($dfaState = self::unmarked($dStates)) !== NULL) {
            $dStates->put($dfaState, true); // Mark

            $possibleInputs = self::getPossibleInputs($dfaState);

            /* > */ self::$inspector->breakpoint('possible_inputs', [
            /* > */    'possible_inputs'    => $possibleInputs['chars'],
            /* > */    'transitions'        => $possibleInputs['transitions'],
            /* > */    'dfa_state_contents' => array_map(function ($s) { return $s->getId(); }, $dfaState->getStates()->toArray())
            /* > */ ]);

            foreach ($possibleInputs['chars'] as $c) {
                $moveResult = self::moveSet($dfaState->getStates(), $c);

                $states = self::epsilonClosureSet($moveResult);
                /* > */ self::$inspector->breakpoint('epsilon_closure', [
                /* > */    'input'  => $moveResult,
                /* > */    'output' => $states
                /* > */ ]);

                $newDfaState = self::getOrCreate($states, $dStates);
                $dfaState->addTransition($newDfaState, [$c]);

                /* > */ self::$inspector->breakpoint('new_dfa_transition', [
                /* > */    'src'  => $dfaState,
                /* > */    'char' => $c,
                /* > */    'dest' => $newDfaState
                /* > */ ]);

                if (!$dStates->hasKey($newDfaState)) {
                    $dStates->put($newDfaState, false);
                }
            }
        }

        $fa = new FiniteAutomaton($initialDfaState);
        $fa->setIds();
        self::setFinalStates($fa);

        return $fa;
    }

    private static function getOrCreate(Set $states, Map $dStates)
    {
        foreach ($dStates->keys() as $dfaState) {
            if ($dfaState->getStates()->diff($states)->isEmpty()) {
                return $dfaState;
            }
        }

        return new DFAState($states);
    }

    private static function setFinalStates(FiniteAutomaton $fa)
    {
        $S = new Stack();
        $visited = new Set();
        $S->push($fa->getInitial());

        while (!$S->isEmpty()) {
            $s = $S->pop();
            if (!$visited->contains($s)) {
                $visited->add($s);

                $tokenTypes = [];

                foreach ($s->getStates() as $nfaState) {
                    if ($nfaState->isFinal()) {
                        $tokenTypes[] = $nfaState->getData();
                        $s->setFinal();
                    }
                }

                if ($s->isFinal()) {
                    $s->setData($tokenTypes);
                }

                foreach ($s->getConnectedStates() as $c => $states) {
                    foreach ($states as $state) {
                        $S->push($state);
                    }
                }
            }
        }
    }

    private static function getPossibleInputs(DFAState $state)
    {
        $chars = new Set();
        $transitions = [];

        foreach ($state->getStates() as $s) {
            foreach ($s->getConnectedStates() as $c => $states) {
                if ($c !== 'ε') {
                    $chars->add($c);
                    $transitions[] = [
                        'src'  => $s,
                        'char' => $c,
                        'dest' => $states
                    ];
                }
            }
        }

        return [
            'chars'       => $chars,
            'transitions' => $transitions
        ];
    }

    private static function unmarked(Map $dStates)
    {
        foreach ($dStates->pairs() as $entry) {
            if (!$entry->value) {
                return $entry->key;
            }
        }

        return null;
    }

    private static function epsilonClosure(Set $result, State $state)
    {
        $S = new Stack();
        $visited = new Set();
        $S->push($state);

        while (!$S->isEmpty()) {
            $s = $S->pop();
            if (!$visited->contains($s)) {
                $visited->add($s);
                $result->add($s);

                foreach ($s->getConnectedStates() as $c => $states) {
                    if ($c !== 'ε') {
                        continue;
                    }

                    foreach ($states as $_s) {
                        $S->push($_s);
                    }
                }
            }
        }
    }

    private static function epsilonClosureSet(Set $T)
    {
        $result = new Set();

        foreach ($T as $s) {
            $intermediateResult = new Set();
            self::epsilonClosure($intermediateResult, $s);
            $result = $result->merge($intermediateResult->toArray());
        }

        return $result;
    }

    private static function move(State $s, string $a)
    {
        $states = new Set($s->getState($a));

        /* > */ self::$inspector->breakpoint('move_states', [
        /* > */    'state'            => $s,
        /* > */    'char'             => $a,
        /* > */    'connected_states' => $states
        /* > */ ]);

        return $states;
    }

    private static function moveSet(Set $T, string $a)
    {
        $result = new Set();

        foreach ($T as $s) {
            $result = $result->merge(self::move($s, $a)->toArray());
        }

        return $result;
    }
}
