<?php

namespace App\Api\Phases\Services;

use App\Core\Inspector;
use App\Core\Lexer\Lexer;

class LexicalAnalysisService
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
    }

    public function run(string $content, array $tokenTypes)
    {
        $lexer = new Lexer($content, $tokenTypes);
        $lexer->getTokens();

        return [
            'dfa'         => $lexer->getDfa(),
            'breakpoints' => $this->inspector->getState('breakpoints')
        ];
    }
}
