<?php

namespace App\Core\Syntax\Regex\TreeTypes;

class Choice extends Regex
{
    private $left;
    private $right;

    public function __construct(Regex $left, Regex $right)
    {
        $this->left = $left;
        $this->right = $right;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function getRight()
    {
        return $this->right;
    }
}
