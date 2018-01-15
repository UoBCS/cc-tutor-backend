<?php

namespace App\Core\Syntax\Grammar;

interface GrammarEntity
{
    public function isTerminal() : bool;
    public function isNonTerminal() : bool;
    public function getName() : string;
}
