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

class FiniteAutomaton implements JsonSerializable
{
    private $initial;
    private $errorState;
    private $alphabet;

    public function __construct(State $initial, Set $alphabet = null)
    {
        $this->initial    = $initial;
        $this->errorState = State::error();
        $this->alphabet   = $alphabet === null ? $this->generateAlphabet() : $alphabet;
    }

    public static function combine(array $fas)
    {
        $combinator = new State();

        foreach ($fas as $fa) {
            $combinator->addTransition($fa->getInitial());
        }

        $nfa = new FiniteAutomaton($combinator);
        $nfa->setIds();

        return $nfa;
    }

    public static function fromRegex(Regex\IRegex $regex, bool $returnRegexTree = false)
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

        return [
            'nfa'        => $fa,
            'regex_tree' => $regexTree
        ];
    }

    public static function fromArray($arr)
    {
        $states = [];

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

        /*foreach ($arr as $entry) {
            if (!isset($states[$entry['src']['id']])) {
                $states[$entry['src']['id']] = new State($entry['src']['id']);
                $states[$entry['src']['id']]->setFinal($entry['src']['final']);
            }

            if (!isset($states[$entry['dest']['id']])) {
                $states[$entry['dest']['id']] = new State($entry['dest']['id']);
                $states[$entry['dest']['id']]->setFinal($entry['dest']['final']);
            }

            $states[$entry['src']['id']]->addTransition($states[$entry['dest']['id']], [$entry['char']]);
        }*/

        return new FiniteAutomaton($states[0]);
    }

    public function getInitial()
    {
        return $this->initial;
    }

    public function setInitial(State $initial)
    {
        $this->initial = $initial;
    }

    public function generateAlphabet()
    {
        $chars = new Set();

        $fn = function ($src, $c, $dest, $arr) use ($chars) {
            $chars->add($c); // TODO: account for any and ranges
        };

        $this->traverse(null, null, $fn, null);

        return $chars;
    }

    // TODO: account for any and ranges
    public function isErrorStateUnreachable()
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

    public function isDeterministic()
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

    public function setIds()
    {
        $fn = function ($s, $id) {
            $s->setId($id++);
            return $id;
        };

        $this->traverse($fn, 0, NULL, NULL);
    }

    public function setDataOnFinalStates(TokenType $token)
    {
        $fn = function ($s) use ($token) {
            if ($s->isFinal()) {
                $s->setData($token);
            }
        };

        $this->traverse($fn, 0, NULL, NULL);
    }

    public function toDfa()
    {
        DfaConverter::init();
        return DfaConverter::toDfa($this->initial);
    }

    public function minimizeDfa()
    {
        while (true) {
            if (!$this->isDeterministic()) {
                throw new AutomatonException('The automaton is not deterministic');
            }

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

            // 2. Construct table
            $table = DiagTable::fromArray($states, $states, function ($s1, $s2) {
                return $s1->isFinal() !== $s2->isFinal();
            });

            do {
                $visited = new Set();
                $finish = true;

                $table->findAndUpdate(function ($s1, $s2, $value) use ($visited, &$finish, $table) {
                    if ($value) {
                        return false;
                    }

                    $chars1 = new Set($s1->getChars());
                    $chars2 = new Set($s2->getChars());
                    $chars = $chars1->union($chars2);

                    foreach ($chars as $char) {
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
            } while (!$finish);

            // Modify DFA
            $unmarkedStates = $table->getHeaderPairs(false);
            $statesToRemove = new Set();

            if (count($unmarkedStates) === 0) {
                break;
            }

            foreach ($unmarkedStates as $statesPair) {
                $q0 = $statesPair[0];
                $q1 = $statesPair[1];

                $dfa = $this->jsonSerialize();

                foreach ($dfa as $transition) {
                    if ($transition['dest'] === $q1) {
                        $transition['src']->addTransition($q0, $transition['char']);
                        $transition['src']->removeTransition($q1, $transition['char']);
                    }
                }
            }
        }

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

    public function __toString()
    {
        $fn = function ($src, $c, $dest, $str) {
            $srcId = $src->getId();
            $destId = $dest->getId();
            return $str . "($src, $c, $dest)\n";
        };

        return $this->traverse(NULL, NULL, $fn, '', 1);
    }
}
