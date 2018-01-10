<?php

namespace App\Core\Syntax\Grammar;

use Ds\Map;
use Ds\Set;
use Ds\Vector;
use JsonSerializable;

class Grammar implements JsonSerializable
{
    private $productions;
    private $terminals;
    private $startSymbol;

    public function getProductions(NonTerminal $lhs) : Vector
    {
        return $this->productions->get($lhs, null);
    }

    public function getAllProductions() : Vector
    {
        return $this->productions;
    }

    public function hasProduction(NonTerminal $lhs, Vector $rhs) : bool
    {
        return false;
        /*if ($this->productions->get($lhs) === null) {
            return false;
        }

        $found = false;
        $rhsItem1 = $rhs->sorted(function ($a, $b) {
            return 1;
        });

        foreach ($productions->get($lhs, []) as $rhsV) {
            foreach ($rhsV as $rhsItem2) {
                if ($rhsItem1->count() !== $rhsItem2->count()) {
                    continue;
                }

                $rhsItem2->sorted(function ($a, $b) {
                    return 1;
                });

                foreach ($rhsItem1 as $index => $r) {
                    if (!$r.equals()) {
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    break;
                }
            }

            if ($found) {
                break;
            }
        }

        //List<List<GrammarEntity>> prods = productions.get(lhs);
        return $found;*/
    }

    public function setProductionsFromData(array $data)
    {
        foreach ($data as $lhs => $rhs) {
            $this->productions->put($lhs, new Vector(array_map(function ($r) { return new Vector($r); }, $rhs)));
        }
    }

    public function getStartSymbol() : NonTerminal
    {
        return $this->startSymbol;
    }

    public function setStartSymbol(NonTerminal $startSymbol)
    {
        $this->startSymbol = $startSymbol;
    }

    public function getNonTerminals() : Set
    {
        return $this->productions->keys();
    }

    public function getTerminals() : Set
    {
        return $this->terminals;
    }

    public function getTerminalByName(string $name) : Terminal
    {
        foreach ($this->terminals as $terminal) {
            if (!$terminal->isEpsilon() && $terminal->getTokenType()->name === $name) {
                return $terminal;
            }
        }

        return null;
    }

    public function getEndOfInputTerminal()
    {
        $eoi = $this->getTerminalByName('EOI');
        return $eoi !== null ? $eoi : $this->getTerminalByName('EOF');
    }

    public function setTerminals(Set $terminals)
    {
        $this->terminals = $terminals;
    }

    public function jsonSerialize()
    {
        return [
            'productions' => $this->productions,
            'terminals'   => $this->terminals,
            'startSymbol' => $this->startSymbol
        ];
    }
}
