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
        if ($this->productions->get($lhs, null) === null) {
            return false;
        }

        $rhsCount = $rhs->count();

        foreach ($this->productions->get($lhs) as $rhs1) {
            if ($rhsCount !== $rhs1->count()) {
                continue;
            }

            $found = true;

            for ($i = 0; $i < $rhsCount; $i++) {
                if (!$rhs[$i]->equals($rhs1[$i])) {
                    $found = false;
                    break;
                }
            }

            if ($found) {
                return true;
            }
        }

        return false;
    }

    public function setFromData(array $data)
    {
        $this->productions = new Map();

        $nonTerminals = array_map(function ($r) { return new NonTerminal($r); }, array_keys($data['productions']));

        foreach ($nonTerminals as $nt) {
            $this->productions->put($nt, null);
        }

        foreach ($data['productions'] as $lhs => $rhs) {
            $this->productions->put(new NonTerminal($lhs), new Vector(array_map(function ($r) {
                return $r === null ? null : new Vector(array_map([$this, 'getGrammarEntityByName'], $r));
            }, $rhs)));
        }

        $this->startSymbol = new NonTerminal($data['start_symbol']);
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
        $nts = array_map(function ($nt) {
            return new NonTerminal($nt);
        }, $this->productions->keys()->toArray());

        return new Set($nts);
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

    public function getGrammarEntityByName($name)
    {
        $ts = $this->terminals;
        $nts = $this->getNonTerminals();

        foreach ($ts as $t) {
            $tokenType = $t->getTokenType();

            if (($name === null && $tokenType === null) || ($tokenType !== null && $name === $tokenType->name)) {
                return $t;
            }
        }

        foreach ($nts as $nt) {
            if ($name === $nt->getName()) {
                return $nt;
            }
        }

        return null;
    }

    public function jsonSerialize()
    {
        $productions = [];

        foreach ($this->productions as $key => $value) {
            $productions[$key->getName()] = $value;
        }

        return [
            'productions' => $productions,
            'terminals'   => $this->terminals,
            'startSymbol' => $this->startSymbol
        ];
    }
}
