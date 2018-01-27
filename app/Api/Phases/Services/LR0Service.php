<?php

namespace App\Api\Phases\Services;

use App\Core\Lexer\Lexer;
use App\Core\Parser\LR0;

class LR0Service
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
    }

    public function parse(string $content, array $tokenTypes, array $grammar, bool $interactive = true)
    {
        $lexer = new Lexer($content, $tokenTypes);
        $parser = new LR0($lexer, $grammar);

        $parser->parse();

        return [
            'tokens'      => $parser->getInput()->getData(),
            //'parse_tree' => $parser->getJsonParseTree(),
            'dfa'         => $parser->getJsonItemsDfa(),
            'breakpoints' => $this->inspector->getState('breakpoints')
        ];
    }
}
