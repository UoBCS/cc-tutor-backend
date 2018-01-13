<?php

namespace App\Core\Parser;

use App\Core\Exceptions\ParserException;
use App\Core\IO\InputStream;
use App\Core\IO\ConsumableInput;
use App\Core\Lexer\Lexer;
use App\Core\Syntax\Grammar\Grammar;
use App\Core\Syntax\Grammar\NonTerminal;
use App\Core\Syntax\Grammar\Terminal;
use App\Core\Syntax\Token\TokenType;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;
use JsonSerializable;

class LL implements JsonSerializable
{
    private $stack;
    private $input;
    private $lexer;
    private $grammar;

    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        $this->stack = new Stack();
        $this->lexer = $lexer;
        $this->input = is_null($lexer) ? new ConsumableInput() : new ConsumableInput($this->lexer->getTokens());
        $this->grammar = new Grammar();

        // Setup grammar object
        if (!is_null($lexer) && count($grammar) > 0) {
            $terminals = array_map(function (TokenType $tokenType) {
                return new Terminal($tokenType);
            }, $this->lexer->getTokenTypes()->toArray());
            $this->grammar->setTerminals(new Set($terminals));
            $this->grammar->setFromData($grammar);

            $this->stack->push($this->grammar->getStartSymbol());
        }
    }

    public static function fromData(array $data) : LL
    {
        $lexer = new Lexer(new InputStream($data['content']), TokenType::fromDataArray($data['tokenTypes']));
        $parser = new LL($lexer, $data['grammar']);
        $parser->setInputIndex($data['inputIndex']);
        if (!is_null($data['stack'])) {
            $parser->setStackFromData($data['stack']);
        }

        return $parser;
    }

    public function getInput() : ConsumableInput
    {
        return $this->input;
    }

    public function setInput(ConsumableInput $input)
    {
        $this->input = $input;
    }

    public function setInputIndex(int $index)
    {
        $this->input->setIndex($index);
    }

    public function getStack() : Stack
    {
        return $this->stack;
    }

    public function setStack(Stack $stack)
    {
        $this->stack = $stack;
    }

    public function setStackFromData($data)
    {
        $this->stack = new Stack();
        $tokenTypes = $this->lexer->getTokenTypes();

        for ($i = count($data) - 1; $i >= 0; $i--) {
            $gEntity = $data[$i];

            if (isset($gEntity['regex'])) {
                $tokenType = $this->lexer->getTokenTypeByName($gEntity['name']);

                if (!is_null($tokenType)) {
                    $this->stack->push(new Terminal($tokenType));
                }
            } else {
                $this->stack->push(new NonTerminal($gEntity));
            }
        }
    }

    public function getGrammar()
    {
        return $this->grammar;
    }

    public function predict(NonTerminal $lhs, Vector $rhs)
    {
        if ($this->stack->isEmpty()) {
            if ($this->input->hasFinished()) {
                // success
            } else {
                $tokens = array_map(function ($token) {
                    return $token->getType()->name;
                }, $this->input->getRemaining());

                $tokensStr = implode(' ', $tokens);

                throw new ParserException("Unexpected input $tokensStr at the end.");
            }
        }

        if (!$this->grammar->hasProduction($lhs, $rhs)) {
            throw new ParserException('The supplied production has not been found.');
        }

        if (!$this->stack->peek()->equals($lhs)) {
            throw new ParserException('Non terminal was not found at the top of the parsing stack.');
        }

        $this->stack->pop();

        for ($i = $rhs->count() - 1; $i >= 0; $i--) {
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

        $inputTerminal = new Terminal($this->input->read()->getType());

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
