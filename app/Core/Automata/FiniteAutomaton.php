<?php

namespace App\Core\Automata;

use App\Core\Syntax\Regex;
use App\Core\Syntax\Token\TokenType;
use Ds\Map;
use Ds\Set;
use Ds\Stack;
use JsonSerializable;

class FiniteAutomaton implements JsonSerializable
{
    private $initial;

    public function __construct(State $initial)
    {
        $this->initial = $initial;
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

        return new FiniteAutomaton($states[0]);
    }

    public function getInitial()
    {
        return $this->initial;
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

        return $this->traverse(NULL, NULL, $fn, [], 1);
    }

    public function __toString()
    {
        $fn = function ($src, $c, $dest, $str) {
            $srcId = $src->getId();
            $destId = $dest->getId();
            return $str . "($srcId, $c, $destId)\n";
        };

        return $this->traverse(NULL, NULL, $fn, '', 1);
    }
}
