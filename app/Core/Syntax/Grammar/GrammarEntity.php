<?php

namespace App\Core\Syntax\Grammar;

use Ds\Hashable;
use JsonSerializable;

interface GrammarEntity extends Hashable, JsonSerializable
{
    public function isTerminal() : bool;
    public function isNonTerminal() : bool;
    public function getName() : string;
}
