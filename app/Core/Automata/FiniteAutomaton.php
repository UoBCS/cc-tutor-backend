<?php

namespace App\Core\Automata;

use App\Core\Exceptions\AutomatonException;
use App\Core\Syntax\Grammar\Terminal;
use App\Core\Syntax\Regex;
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Utils\Ds\DiagTable;
use App\Infrastructure\Utils\Ds\Pair;
use Ds\Map;
use Ds\Set;
use Ds\Stack;
use JsonSerializable;

/**
 * Represents a finite automaton
 */
class FiniteAutomaton implements JsonSerializable
{
    private $initial;
    private $errorState;
    private $alphabet;

    /**
     * Creates a new finite automaton
     *
     * @param State $initial
     * @param Set $alphabet
     */
    public function __construct(State $initial, Set $alphabet = null)
    {
        $this->initial    = $initial;
        $this->alphabet   = $alphabet === null ? $this->generateAlphabet() : $alphabet;
        $this->errorState = State::error();
    }

    /**
     * Combines an array of finite automata
     *
     * @param array $fas
     * @return self
     */
    public static function combine(array $fas) : self
    {
        $combinator = new State();

        foreach ($fas as $fa) {
            $combinator->addTransition($fa->getInitial());
        }

        $nfa = new FiniteAutomaton($combinator);
        $nfa->setIds();

        return $nfa;
    }

    /**
     * Constructs a finite automaton from a regular expression
     *
     * @param Regex\IRegex $regex
     * @param boolean $returnRegexTree
     * @return mixed
     */
    public static function fromRegex(Regex\IRegex $regex, bool $returnRegexTree = true)
    {
        // Build regex tree
        $regexParser = new Regex\RegexParser($regex->getRegex());
        $regexTree = $regexParser->parse();

        // Build NFA
        FiniteAutomatonBuilder::init();
        $result = FiniteAutomatonBuilder::fromRegexTree($regexTree);
        $fa = $result->getFiniteAutomaton();

        $fa->setIds();

        if ($regex instanceof TokenType) {
            $fa->setDataOnFinalStates($regex);
        }

        return $returnRegexTree
                ? ['nfa' => $fa, 'regex_tree' => $regexTree]
                : $fa;
    }

    /**
     * Constructs a finite automaton from the array representation
     *
     * @param array $arr
     * @return self
     */
    public static function fromArray(array $arr) : self
    {
        $states = [];
        $notFlat = isset($arr['states']) && isset($arr['transitions']);

        if ($notFlat) {
            // Add states
            foreach ($arr['states'] as $state) {
                if (!isset($states[$state['id']])) {
                    $states[$state['id']] = new State($state['id']);

                    if (isset($state['final'])) {
                        $states[$state['id']]->setFinal($state['final']);
                    }

                    if (isset($state['data'])) {
                        $states[$state['id']]->setData($state['data']);
                    }
                }
            }

            // Add transitions
            foreach ($arr['transitions'] as $transition) {
                if (isset($states[$transition['src']])
                && isset($states[$transition['dest']])) {
                    $states[$transition['src']]->addTransition($states[$transition['dest']], $transition['char']);
                }
            }
        } else {
            foreach ($arr as $entry) {
                if (!isset($states[$entry['src']['id']])) {
                    $states[$entry['src']['id']] = new State($entry['src']['id']);
                    $states[$entry['src']['id']]->setFinal($entry['src']['final']);
                }

                if (!isset($states[$entry['dest']['id']])) {
                    $states[$entry['dest']['id']] = new State($entry['dest']['id']);
                    $states[$entry['dest']['id']]->setFinal($entry['dest']['final']);
                }

                $states[$entry['src']['id']]->addTransition($states[$entry['dest']['id']], [$entry['char']]);
            }
        }

        return new FiniteAutomaton($states[0]);
    }

    /**
     * Returns the initial state
     *
     * @return State
     */
    public function getInitial() : State
    {
        return $this->initial;
    }

    /**
     * Sets the initial state
     *
     * @param State $initial
     * @return void
     */
    public function setInitial(State $initial) : void
    {
        $this->initial = $initial;
    }

    /**
     * Generate the alphabet of the finite automaton
     *
     * @return Set
     */
    public function generateAlphabet() : Set
    {
        $chars = new Set();

        $fn = function ($src, $c, $dest, $arr) use ($chars) {
            $chars->add($c);
        };

        $this->traverse(null, null, $fn, null);

        return $chars;
    }

    /**
     * Checks if the error state is unreachable
     *
     * @return boolean
     */
    public function isErrorStateUnreachable() : bool
    {
        $foundDifference = false;

        $fn = function ($s) use (&$foundDifference) {
            $charSet = new Set($s->getChars());
            if (!$this->alphabet->diff($charSet)->isEmpty()) {
                $foundDifference = true;
            }
        };

        $this->traverse($fn, null, null, null);

        return !$foundDifference;
    }

    /**
     * Checks if the finite automaton is deterministic
     *
     * @return boolean
     */
    public function isDeterministic() : bool
    {
        $fn = function ($s, $data) {
            foreach ($s->getConnectedStates() as $char => $states) {
                if ($char === Terminal::EPSILON || count($states) > 1) {
                    return $data && false;
                }
            }

            return $data && true;
        };

        return $this->traverse($fn, true, null, null, 0);
    }

    /**
     * Traverses the finite automaton
     *
     * @param callable $fn1
     * @param mixed $data1
     * @param callable $fn2
     * @param mixed $data2
     * @param integer $return
     * @return mixed
     */
    public function traverse($fn1, $data1, $fn2, $data2, $return = -1)
    {
        $S = new Stack();
        $visited = new Set();

        $S->push($this->initial);

        while (!$S->isEmpty()) {
            $s = $S->pop();
            if (!$visited->contains($s)) {
                $visited->add($s);

                if (isset($fn1)) {
                    $data1 = call_user_func($fn1, $s, $data1);
                }

                foreach ($s->getConnectedStates() as $c => $states) {
                    foreach ($states as $state) {
                        if (isset($fn2)) {
                            $data2 = call_user_func($fn2, $s, $c, $state, $data2);
                        }

                        $S->push($state);
                    }
                }
            }
        }

        if ($return === 0) {
            return $data1;
        } else if ($return === 1) {
            return $data2;
        } else if ($return === 2) {
            return [
                'data1' => $data1,
                'data2' => $data2
            ];
        }
    }

    /**
     * Sets incremental state IDs in the automaton
     *
     * @return void
     */
    public function setIds() : void
    {
        $fn = function ($s, $id) {
            $s->setId($id++);
            return $id;
        };

        $this->traverse($fn, 0, null, null);
    }

    /**
     * Sets token data in final states
     *
     * @param TokenType $token
     * @return void
     */
    public function setDataOnFinalStates(TokenType $token)
    {
        $fn = function ($s) use ($token) {
            if ($s->isFinal()) {
                $s->setData($token);
            }
        };

        $this->traverse($fn, 0, null, null);
    }

    /**
     * Converts a non-deterministic finite automaton to a deterministic one
     *
     * @return self
     */
    public function toDfa() : self
    {
        DfaConverter::init();
        return DfaConverter::toDfa($this->initial);
    }

    /**
     * Checks whether the automaton accepts the given word
     *
     * @param string $word
     * @return boolean
     */
    public function accepts(string $word) : bool
    {
        $inspector = inspector();
        $inspector->createStore('breakpoints', 'array');

        return $this->_accepts($word, $this->initial, $inspector);
    }

    /**
     * Minimizes the DFA
     *
     * @return array
     */
    public function minimizeDfa() : array
    {
        $inspector = inspector();
        $inspector->createStore('breakpoints', 'array');

        while (true) {
            if (!$this->isDeterministic()) {
                throw new AutomatonException('The automaton is not deterministic');
            }

            /* > */ $inspector->breakpoint('input_dfa', [
            /* > */    'dfa' => $this->jsonSerialize()
            /* > */ ]);

            // 1. Get all reachable states
            $states = [];

            $fn = function ($s) use (&$states) {
                $states[] = $s;
            };

            $this->traverse($fn, null, null, null);

            if (!$this->isErrorStateUnreachable()) {
                $states[] = $this->errorState;
            }

            usort($states, function ($s1, $s2) {
                return $s1->getId() - $s2->getId();
            });

            /* > */ $inspector->breakpoint('reachable_states', [
            /* > */    'states' => $states
            /* > */ ]);

            // 2. Construct table
            $table = DiagTable::fromArray($states, $states, function ($s1, $s2) {
                return $s1->isFinal() !== $s2->isFinal();
            });

            /* > */ $inspector->breakpoint('initial_table', [
            /* > */    'table' => $table
            /* > */ ]);

            do {
                $finish = true;

                $table->findAndUpdate(function ($s1, $s2, $value) use (&$finish, $table) {
                    if ($value) {
                        return false;
                    }

                    foreach ($this->alphabet as $char) {
                        $connectedStates1 = $s1->getState($char);
                        $connectedStates2 = $s2->getState($char);

                        if ($table->get(
                            count($connectedStates1) === 0 ? $this->errorState : $connectedStates1[0],
                            count($connectedStates2) === 0 ? $this->errorState : $connectedStates2[0]
                        )) {
                            $finish = false;
                            return true;
                        }
                    }

                    return false;
                }, true);

                /* > */ $inspector->breakpoint('updated_table', [
                /* > */    'table' => $table
                /* > */ ]);
            } while (!$finish);

            // Modify DFA
            $unmarkedStates = $table->getHeaderPairs(false);
            $statesToRemove = new Set();

            if (count($unmarkedStates) === 0) {
                break;
            }

            /* > */ $inspector->breakpoint('unmarked_states', [
            /* > */    'states' => $unmarkedStates
            /* > */ ]);

            foreach ($unmarkedStates as $statesPair) {
                $q0 = $statesPair[0];
                $q1 = $statesPair[1];

                $dfa = $this->jsonSerialize();

                foreach ($dfa as $transition) {
                    if ($transition['dest'] === $q1) {
                        $transition['src']->addTransition($q0, $transition['char']);
                        if (is_array($q0->getData()) && is_array($q1->getData())) {
                            $q0->setData(array_merge($q0->getData(), $q1->getData()));
                        }
                        $transition['src']->removeTransition($q1, $transition['char']);
                    }
                }
            }

            /* > */ $inspector->breakpoint('updated_dfa', [
            /* > */    'dfa' => $this->jsonSerialize()
            /* > */ ]);
        }

        /* > */ $inspector->breakpoint('finish', null);

        return $this->jsonSerialize();
    }

    public function jsonSerialize()
    {
        $fn = function ($src, $c, $dest, $arr) {
            $arr[] = [
                'src'  => $src,
                'char' => $c,
                'dest' => $dest
            ];
            return $arr;
        };

        return $this->traverse(null, null, $fn, [], 1);
    }

    public function toArray()
    {
        $states = [];

        $fnStates = function ($state) use (&$states) {
            $states[] = $state->jsonSerialize();
        };

        $fnTransitions = function ($src, $c, $dest) use (&$transitions) {
            $transitions[] = [
                'src'  => $src->getId(),
                'char' => $c,
                'dest' => $dest->getId()
            ];
        };

        $this->traverse($fnStates, null, $fnTransitions, null);

        return [
            'states'      => $states,
            'transitions' => $transitions
        ];
    }

    public function __toString()
    {
        $fn = function ($src, $c, $dest, $str) {
            $srcId = $src->getId();
            $destId = $dest->getId();
            return $str . "($src, $c, $dest)\n";
        };

        return $this->traverse(null, null, $fn, '', 1);
    }

    private function _accepts(string $word, State $state, $inspector) : bool
    {
        if ($word === '') {
            /* > */ $inspector->breakpoint('accepts_stop', [
            /* > */    'result' => $state->isFinal(),
            /* > */ ]);

            return $state->isFinal();
        }

        $states = $state->getState($word[0]);

        if (count($states) === 0) {
            return false;
        }

        $destState = $states[0];

        /* > */ $inspector->breakpoint('accepts_step', [
        /* > */    'transition' => ['src' => $state, 'char' => $word[0], 'dest' => $destState],
        /* > */ ]);

        return $this->_accepts(substr($word, 1), $destState, $inspector);
    }
}
