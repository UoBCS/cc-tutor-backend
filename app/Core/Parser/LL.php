<?php

namespace App\Core\Parser;

use App\Core\Exceptions\ParserException;
use App\Core\IO\ConsumableInput;
use App\Core\Lexer\Lexer;
use App\Core\Syntax\Grammar\Grammar;
use App\Core\Syntax\Grammar\NonTerminal;
use App\Core\Syntax\Grammar\Terminal;
use App\Core\Syntax\Token\TokenType;
use Ds\Stack;
use Ds\Vector;
use JsonSerializable;

class LL implements JsonSerializable
{
    private $stack;
    private $input;
    private $lexer;
    private $grammar;

    public function __construct(Lexer $lexer, array $grammar)
    {
        $this->stack = new Stack();
        $this->lexer = lexer;
        $this->input = new ConsumableInput($this->lexer->getTokens());
        $this->grammar = new Grammar();

        // Setup grammar object
        $terminals = array_map(function (TokenType $tokenType) {
            return new Terminal($tokenType);
        }, $this->lexer->getTokenTypes());
        $this->grammar->setTerminals(new Set($terminals));
        $this->grammar->setProductionsFromData($grammar);
    }

    public function predict(NonTerminal $lhs, Vector $rhs)
    {
        if ($this->stack->isEmpty()) {
            if ($this->input->hasFinished()) {
                // success
            } else {
                $tokens = array_map(function ($token) {
                    return $token->getType()->name;
                }, $input->getRemaining());

                $tokensStr = implode(' ', $tokens);

                throw new ParserException("Unexpected input $tokensStr at the end.");
            }
        }

        if (!$this->stack->peek()->equals($lhs)) {
            throw new ParserException('Non terminal was not found at the top of the parsing stack.');
        }

        $this->stack->pop();

        for ($i = $rhs->count() - 1; $i >= 0; $i++) {
            $this->stack->push($rhs[$i]);
        }
    }

    public function match()
    {
        if ($this->input->hasFinished()) {
            if ($this->stack->isEmpty()) {
                // success
            } else {
                throw new ParserException('Premature end of input.');
            }
        }

        $inputTerminal = new Terminal($this->input->read()->getTokenType());

        if (!$this->stack->peek()->isTerminal()) {
            throw new ParserException('Could not match a terminal with a non-terminal.');
        }

        if (!$this->stack->peek()->equals($inputTerminal)) {
            throw new ParserException('Expecting ' . $this->stack->peek()->getName() . '; found ' . $inputTerminal->getName() . ' in the input instead.');
        }

        $this->stack->pop();
        $this->input->advance();
    }

    public function jsonSerialize()
    {
        return [
            'stack'   => $this->stack,
            'input'   => $this->input,
            'grammar' => $this->grammar
        ];
    }
}
