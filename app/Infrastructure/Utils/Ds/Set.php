<?php

namespace App\Infrastructure\Utils\Ds;

class Set
{
    private $set = [];

    public function add($v)
    {
        $this->set[serialize($v)] = null;
    }

    public function contains($v)
    {
        return in_array(serialize($v), array_keys($this->set), true);
    }
}
