<?php

namespace App\Core\Syntax\Regex\TreeTypes;

class GroupItems extends Regex
{
    private $items = [];

    public function addItem(Regex $item)
    {
        $this->items[] = $item;
    }

    public function getItems()
    {
        return $this->items;
    }
}
