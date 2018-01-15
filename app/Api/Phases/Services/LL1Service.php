<?php

namespace App\Api\Phases\Services;

use App\Core\Lexer\Lexer;
use App\Core\Parser\LL1;

class LL1Service
{
    public function parse(string $content, array $tokenTypes, array $grammar, bool $interactive = true)
    {
        $lexer = new Lexer($content, $tokenTypes);
        $parser = new LL1($lexer, $grammar);

        return $parser->parse();
    }
}
