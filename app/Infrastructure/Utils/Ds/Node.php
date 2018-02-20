<?php

namespace App\Infrastructure\Utils\Ds;

use JsonSerializable;
use Tree\Node\Node as BaseNode;

class Node extends BaseNode implements JsonSerializable
{
    public function jsonSerialize()
    {
        return $this->getValue();
    }

    public function __toString()
    {
        return strval($this->getValue());
    }
}
