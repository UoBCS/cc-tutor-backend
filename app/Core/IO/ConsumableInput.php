<?php

namespace App\Core\IO;

use Exception;
use JsonSerializable;

class ConsumableInput implements JsonSerializable
{
    private $input;
    private $index;

    public function __construct($input = null)
    {
        $this->input = $input;
        $this->index = 0;
    }

    public function hasFinished() : bool
    {
        return $this->index >= count($this->input);
    }

    public function read()
    {
        if ($this->hasFinished()) {
            throw new Exception('Cannot read input.');
        }

        return $this->input[$this->index];
    }

    public function advance()
    {
        if ($this->hasFinished()) {
            throw new Exception('Cannot advance input.');
        }

        return $this->input[$this->index++];
    }

    public function getRemaining()
    {
        if ($this->hasFinished()) {
            throw new Exception('Cannot advance input.');
        }

        return array_slice($this->input, $this->index);
    }

    public function jsonSerialize()
    {
        return [$this->input, $this->index];
    }
}
