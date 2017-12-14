<?php

namespace App\Core;

class Inspector
{
    private $state = [];

    public function update($key, $data)
    {
        $this->state[$key] = $data;
    }

    public function updateArray($key, $item)
    {
        if (!isset($this->state[$key])) {
            $this->state[$key] = [];
        }
        $this->state[$key][] = $item;
    }

    public function getState()
    {
        return $this->state;
    }

    public function reset()
    {
        $this->state = [];
    }
}
