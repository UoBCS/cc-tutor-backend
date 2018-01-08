<?php

namespace App\Api\Phases\Services;

use App\Core\Inspector;
use App\Core\IO\InputStream;
use App\Core\Lexer\Lexer;
use App\Core\Syntax\Token\TokenType;

class PhaseService
{
    private $inspector;

    public function __construct()
    {
        $this->inspector = inspector();
        //$this->inspector->getState('breakpoints')
    }

    public function lexicalAnalysis(string $content, array $tokenTypes)
    {
        $lexer = new Lexer(new InputStream($content), TokenType::fromDataArray($tokenTypes));
        return $lexer->getTokens();
    }
}
