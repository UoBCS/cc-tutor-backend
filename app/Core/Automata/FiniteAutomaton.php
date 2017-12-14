<?php

namespace App\Core\Automata;

use App\Core\Syntax\Regex;
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Utils\Ds\Set;

class FiniteAutomaton
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

    public static function fromRegex(Regex\IRegex $regex)
    {
        $regexParser = new Regex\RegexParser($regex->getRegex());
        $regexTree = $regexParser->parse();

        // Build NFA
        $result = FiniteAutomatonBuilder::fromRegexTree($regexTree);
        $fa = $result->getFiniteAutomaton();

        $fa->setIds();

        if ($regex instanceof TokenType) {
            $fa->setDataOnFinalStates($regex);
        }

        return $fa;
    }

    public function getInitial()
    {
        return $initial;
    }

    public function setIds()
    {
        $S = [];
        $visited = new Set();

        array_push($S, $this->initial);
        $id = 0;

        while (count($S) !== 0) {
            $s = array_pop($S);
            if (!$visited->contains($s)) { //!in_array(serialize($s), array_keys($visited), true)) {
                //$visited[serialize($s)] = null;
                $visited->add($s);

                $s->setId($id++);

                foreach ($s->getConnectedStates() as $c => $states) {
                    foreach ($states as $state) {
                        array_push($S, $state);
                    }
                }
            }
        }
    }

    public function setDataOnFinalStates(TokenType $token)
    {
        $S = [];
        $visited = new Set();

        array_push($S, $this->initial);

        while (count($S) !== 0) {
            $s = array_pop($S);
            if (!$visited->contains($s)) { //!in_array(serialize($s), array_keys($visited), true)) {
                //$visited[(string) $s] = null;
                $visited->add($s);

                if ($s->isFinal()) {
                    $s->setData($token);
                }

                foreach ($s->getConnectedStates() as $c => $states) {
                    foreach ($states as $state) {
                        array_push($S, $state);
                    }
                }
            }
        }
    }

    public function __toString()
    {
        $S = [];
        $visited = new Set();
        $str = '';

        array_push($S, $this->initial);

        while (count($S) !== 0) {
            $s = array_pop($S);
            if (!$visited->contains($s)) {
                $visited->add($s);

                foreach ($s->getConnectedStates() as $c => $states) {
                    foreach ($states as $state) {
                        $srcId = $s->getId();
                        $destId = $state->getId();
                        $str .= "($srcId, $c, $destId)\n";

                        array_push($S, $state);
                    }
                }
            }
        }

        return $str;
    }
}
