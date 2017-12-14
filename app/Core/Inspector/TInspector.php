<?php

namespace App\Core\Inspector;

trait TInspector
{
    private $state;

    public function collect($data)
    {
        $state[] = $data;
    }

    public function reset()
    {
        $state = [];
    }
}
