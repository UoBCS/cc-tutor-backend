<?php

namespace App\Api\Phases\Services;

use App\Core\Lexer\Lexer;
use App\Core\Parser\LL1;

class LL1Service
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
    }

    public function parse(string $content, array $tokenTypes, array $grammar, bool $interactive = true)
    {
        $lexer = new Lexer($content, $tokenTypes);
        $parser = new LL1($lexer, $grammar);

        $parser->parse();

        return [
            'tokens'      => $parser->getInput()->getData(),
            'breakpoints' => $this->inspector->getState('breakpoints')
        ];
    }
}
