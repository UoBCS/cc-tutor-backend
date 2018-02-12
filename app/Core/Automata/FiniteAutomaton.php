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

    public function __construct(State $initial)
    {
        $this->initial = $initial;
        $this->errorState = State::error();
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

        return $this->traverse($fn, true, NULL, NULL, 0);
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
        if (!$this->isDeterministic()) {
            throw new AutomatonException('The automaton is not deterministic');
        }

        // Step 1. Remove unreachable states: this is already implemented by the nature of State

        // Step 2. Collapse equivalent states

        // 2.1. Get all reachable states
        $states = [];

        $fn = function ($s) use (&$states) {
            $states[] = $s;
        };

        $this->traverse($fn, null, null, null);

        $states[] = $this->errorState;

        // 2.2. Construct table
        $table = DiagTable::fromArray($states, $states, function ($s1, $s2) {
            return ($s1->isError() || $s2->isError()) || ($s1->isFinal() !== $s2->isFinal());
        });

        do {
            $visited = new Set();
            $finish = true;

            $table->findAndUpdate(function ($i, $j, $s1, $s2, $value) use ($visited, &$finish, $table) {
                /*if ($visited->contains(new Pair($i, $j)) || $visited->contains(new Pair($j, $i))) {
                    return false;
                }*/

                if ($value) {
                    return false;
                }

                //$visited->add(new Pair($i, $j));
                //$visited->add(new Pair($j, $i));

                $chars1 = new Set(array_keys($s1->getConnectedStates()));
                $chars2 = new Set(array_keys($s2->getConnectedStates()));

                //var_dump($i, $chars1);
                //var_dump($j, $chars2);

                /*if (count($chars1) !== count($chars2)) {
                    $finish = false;
                    return true;
                }*/

                $chars = $chars1->union($chars2); //array_unio($chars1, $chars2);

                /*if (count($chars) !== count($chars1)) {
                    $finish = false;
                    return true;
                }*/

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
            }, true, true, true);
        } while (!$finish);

        var_dump($table->getContents());

        $unmarkedStates = $table->getHeaderPairs(false, true);

        $dfa = $this->jsonSerialize();
        //$newDfa = $dfa;

        foreach ($unmarkedStates as $statesPair) {
            $q0 = $statesPair[0];
            $q1 = $statesPair[1];

            foreach ($dfa as $transition) {
                if ($transition['dest'] === $q1) {
                    $transition['src']->addTransition($q0, $transition['char']);
                    $transition['src']->removeTransition($q1, $transition['char']);
                }

                if ($transition['src'] === $q1) {
                    $q0->addTransition($transition['dest'], $transition['char']);
                    $q1->removeTransition($transition['dest'], $transition['char']);
                }
            }
        }

        //var_dump($newDfa);

        //$this->updateTransitions($this->initial, $newDfa);

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
