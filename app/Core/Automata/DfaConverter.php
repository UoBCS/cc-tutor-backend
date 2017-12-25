<?php

namespace App\Core\Automata;

use Ds\Map;
use Ds\Set;
use Ds\Stack;

class DfaConverter
{
    public static function toDfa(State $initial)
    {
        $epsilonReachableStates = new Set();
        self::epsilonClosure($epsilonReachableStates, $initial);

        $initialDfaState = new DfaState($epsilonReachableStates);

        $dStates = new Map();

        $dStates->put($initialDfaState, false);

        while (($dfaState = self::unmarked($dStates)) !== NULL) {
            $dStates->put($dfaState, true); // Mark

            $possibleInputs = self::getPossibleInputs($dfaState);
            foreach ($possibleInputs as $c) {
                $states = self::epsilonClosureSet(self::moveSet($dfaState->getStates(), $c));

                $newDfaState = self::getOrCreate($states, $dStates);
                $dfaState->addTransition($newDfaState, [$c]);

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

        foreach ($state->getStates() as $s) {
            foreach ($s->getConnectedStates() as $c => $states) {
                if ($c !== 'ε') {
                    $chars->add($c);
                }
            }
        }

        return $chars;
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
        return new Set($s->getState($a));
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
