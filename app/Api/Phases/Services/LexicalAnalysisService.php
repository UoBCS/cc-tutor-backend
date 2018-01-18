<?php

namespace App\Api\Phases\Services;

use App\Core\Inspector;
use App\Core\IO\InputStream;
use App\Core\Lexer\Lexer;
use App\Core\Syntax\Token\TokenType;

class LexicalAnalysisService
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
    }

    public function run(string $content, array $tokenTypes)
    {
        $lexer = new Lexer(new InputStream($content), TokenType::fromDataArray($tokenTypes));
        $lexer->getTokens();

        return [
            'dfa'         => $lexer->getDfa(),
            'breakpoints' => $this->inspector->getState('breakpoints')
        ];
    }
}
