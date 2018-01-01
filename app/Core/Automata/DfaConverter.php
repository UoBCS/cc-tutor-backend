<?php

namespace App\Core\Automata;

use Ds\Map;
use Ds\Set;
use Ds\Stack;

class DfaConverter
{
    private static $inspector;

    public static function init()
    {
        self::$inspector = inspector();
        self::$inspector->createStore('breakpoints', 'array');
        self::$inspector->setRootFn('toDfa');
    }

    public static function toDfa(State $initial)
    {
        /* > */ self::$inspector->breakpoint('highlight_initial_nfa_state', [ 'state' => $initial ], __FUNCTION__);

        $epsilonReachableStates = new Set();

        self::epsilonClosure($epsilonReachableStates, $initial);
        /* > */ self::$inspector->breakpoint('initial_state_epsilon_closure', [
        /* > */    'initial'          => $initial,
        /* > */    'reachable_states' => $epsilonReachableStates
        /* > */ ], __FUNCTION__);

        $initialDfaState = new DfaState($epsilonReachableStates);
        /* > */ self::$inspector->breakpoint('initial_dfa_state', $initialDfaState, __FUNCTION__);

        $dStates = new Map();

        $dStates->put($initialDfaState, false);

        while (($dfaState = self::unmarked($dStates)) !== NULL) {
            $dStates->put($dfaState, true); // Mark

            $possibleInputs = self::getPossibleInputs($dfaState);

            /* > */ self::$inspector->breakpoint('possible_inputs', [
            /* > */    'possible_inputs'    => $possibleInputs['chars'],
            /* > */    'transitions'        => $possibleInputs['transitions'],
            /* > */    'dfa_state_contents' => array_map(function ($s) { return $s->getId(); }, $dfaState->getStates()->toArray())
            /* > */ ], __FUNCTION__);

            foreach ($possibleInputs['chars'] as $c) {
                /* > */ self::$inspector->stepInto('moveSet', __FUNCTION__);
                $moveResult = self::moveSet($dfaState->getStates(), $c);
                /* > */ self::$inspector->stepOut();

                $states = self::epsilonClosureSet($moveResult);
                /* > */ self::$inspector->breakpoint('epsilon_closure', [
                /* > */    'input'  => $moveResult,
                /* > */    'output' => $states
                /* > */ ], __FUNCTION__);

                $newDfaState = self::getOrCreate($states, $dStates);
                $dfaState->addTransition($newDfaState, [$c]);

                /* > */ self::$inspector->breakpoint('new_dfa_transition', [
                /* > */    'src'  => $dfaState,
                /* > */    'char' => $c,
                /* > */    'dest' => $newDfaState
                /* > */ ], __FUNCTION__);

                if (!$dStates->hasKey($newDfaState)) {
                    $dStates->put($newDfaState, false);
                }
            }
        }

        $fa = new FiniteAutomaton($initialDfaState);
        //fa.optimise();
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

        return NULL;
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
        /* > */ ], __FUNCTION__);

        return $states;
    }

    private static function moveSet(Set $T, string $a)
    {
        $result = new Set();

        foreach ($T as $s) {
            /* > */ self::$inspector->stepInto('move', __FUNCTION__);
            $result = $result->merge(self::move($s, $a)->toArray());
            /* > */ self::$inspector->stepOut();
        }

        return $result;
    }
}
