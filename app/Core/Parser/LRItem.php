<?php

namespace App\Core\Parser;

use App\Core\Exceptions\ParserException;
use App\Core\Syntax\Grammar\GrammarEntity;
use App\Core\Syntax\Grammar\NonTerminal;
use Ds\Hashable;
use JsonSerializable;

class LRItem implements Hashable, JsonSerializable
{
    private $lhs;
    private $rhs;
    private $dotIndex;

    public function __construct(NonTerminal $lhs = null, array $rhs = [], $dotIndex = 0)
    {
        $this->lhs      = $lhs;
        $this->rhs      = $rhs;
        $this->dotIndex = $dotIndex;
    }

    public function getLhs() : NonTerminal
    {
        return $this->lhs;
    }

    public function setLhs(NonTerminal $lhs)
    {
        $this->lhs = $lhs;
    }

    public function getRhs() : array
    {
        return $this->rhs;
    }

    public function setRhs(array $rhs)
    {
        $this->rhs = $rhs;
    }

    public function getDotIndex() : int
    {
        return $this->dotIndex;
    }

    public function setDotIndex(int $dotIndex)
    {
        $this->dotIndex = $dotIndex;
    }

    public function getNext() : GrammarEntity
    {
        if ($this->dotIsAtTheEnd()) {
            throw new ParserException('Dot reached the end.');
        }

        return $this->rhs[$this->dotIndex];
    }

    public function advanceDot()
    {
        $this->dotIndex++;
    }

    public function dotIsAtTheEnd()
    {
        return $this->dotIndex >= count($this->rhs);
    }

    public function equals($obj) : bool
    {
        if (!($obj instanceof LRItem)) {
            return false;
        }

        if (!$this->lhs->equals($obj->lhs) || $this->dotIndex !== $obj->dotIndex) {
            return false;
        }

        if (count($this->rhs) !== count($obj->rhs)) {
            return false;
        }

        for ($i = 0; $i < count($this->rhs); $i++) {
            if (arrayFind($obj->rhs, $this->rhs[$i]) === -1) {
                return false;
            }
        }

        return true;
    }

    public function hash()
    {
        $str = '';

        $str += $this->lhs->hash() + '~';
        $str += strval($this->dotIndex) + '~';

        array_reduce($this->rhs, function ($s, $grammarEntity) {
            return $s + $grammarEntity->hash();
        }, '[');

        $str += ']';

        return $str;
    }

    public function jsonSerialize()
    {
        return [
            'lhs'      => $this->lhs,
            'rhs'      => $this->rhs,
            'dotIndex' => $this->dotIndex
        ];
    }

    public function __clone()
    {
        $o = new LRItem();

        $this->lhs = clone $this->lhs;
        $o->setLhs($this->lhs);
        $o->setRhs(deepCloneArray($this->rhs));
        $o->setDotIndex($this->dotIndex);

        return $o;
    }
}
