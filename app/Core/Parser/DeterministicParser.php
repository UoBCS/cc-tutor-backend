<?php

namespace App\Core\Parser;

use App\Core\Exceptions\ParserException;
use App\Core\IO\InputStream;
use App\Core\IO\ConsumableInput;
use App\Core\Lexer\Lexer;
use App\Core\Syntax\Grammar\Grammar;
use App\Core\Syntax\Grammar\GrammarEntity;
use App\Core\Syntax\Grammar\NonTerminal;
use App\Core\Syntax\Grammar\Terminal;
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Utils\Ds\Node;
use Ds\Map;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;

abstract class DeterministicParser
{
    protected $stack;
    protected $input;
    protected $lexer;
    protected $grammar;
    protected $parseTree;
    protected $inspector;

    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        $this->stack = new Stack();
        $this->lexer = $lexer;
        $this->input = $lexer === null ? new ConsumableInput() : new ConsumableInput($this->lexer->getTokens());
        $this->grammar = new Grammar();

        if ($lexer !== null && count($grammar) > 0) {
            $this->grammar->setTerminals($this->lexer->getTerminals());
            $this->grammar->setFromData($grammar);

            $this->initialize();
            $this->initializeStack();
            $this->initializeParseTree();

            // Compute parsing table for non-interactive mode
            //$this->computeParsingTable();
        }

        $this->inspector = inspector();
        $this->inspector->createStore('breakpoints', 'array');
    }

    public function getStack() : Stack
    {
        return $this->stack;
    }

    public function getInput() : ConsumableInput
    {
        return $this->input;
    }

    public function getLexer() : Lexer
    {
        return $this->lexer;
    }

    public function getGrammar() : Grammar
    {
        return $this->grammar;
    }

    public function getParseTree() : array
    {
        return $this->parseTree;
    }

    public function initialize()
    {

    }

    abstract function initializeStack();

    abstract function initializeParseTree();

    abstract function parse() : bool;
}
