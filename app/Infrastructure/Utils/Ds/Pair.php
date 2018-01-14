<?php

namespace App\Infrastructure\Utils\Ds;

use Ds\Hashable;
use JsonSerializable;

class Pair implements Hashable, JsonSerializable
{
    private $fst;
    private $snd;

    public function __construct($fst, $snd)
    {
        $this->fst = $fst;
        $this->snd = $snd;
    }

    public function getFst()
    {
        return $this->fst;
    }

    public function getSnd()
    {
        return $this->snd;
    }

    public function equals($obj) : bool
    {
        return
            (
                (is_object($this->fst) && is_object($obj->fst) && $this->fst->equals($obj->fst))
                || ($this->fst === $obj->fst)
            )
            &&
            (
                (is_object($this->snd) && is_object($obj->snd) && $this->snd->equals($obj->snd))
                || ($this->snd === $obj->snd)
            )
        ;
    }

    public function hash() : string
    {
        $h1 = is_object($this->fst) ? $this->fst->hash() : strval($this->fst);
        $h2 = is_object($this->snd) ? $this->snd->hash() : strval($this->snd);

        return "$h1h2";
    }

    public function jsonSerialize()
    {
        return [$this->fst, $this->snd];
    }
}
