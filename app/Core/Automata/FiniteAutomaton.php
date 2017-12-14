<?php

namespace App\Core\Automata;

use App\Core\Syntax\Regex;
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Utils\Ds\Set;
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

    public function getInitial()
    {
        return $initial;
    }

    public function traverse($fn1, $data1, $fn2, $data2, $return = -1)
    {
        $S = [];
        $visited = new Set();

        array_push($S, $this->initial);

        while (count($S) !== 0) {
            $s = array_pop($S);
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

                        array_push($S, $state);
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

        $this->traverse($fn, 0, null, null);
    }

    public function setDataOnFinalStates(TokenType $token)
    {
        $fn = function ($s) use ($token) {
            if ($s->isFinal()) {
                $s->setData($token);
            }
        };

        $this->traverse($fn, 0, null, null);
    }

    public function jsonSerialize()
    {
        return [];
    }

    public function __toString()
    {
        $fn = function ($src, $c, $dest, $str) {
            $srcId = $src->getId();
            $destId = $dest->getId();
            return $str . "($srcId, $c, $destId)\n";
        };

        return $this->traverse(null, null, $fn, '', 1);
    }
}
