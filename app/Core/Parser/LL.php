<?php

namespace App\Core\Parser;

use App\Core\Exceptions\ParserException;
use App\Core\Lexer\Lexer;
use App\Core\Syntax\Grammar\NonTerminal;
use App\Core\Syntax\Grammar\Terminal;
use App\Infrastructure\Utils\Ds\Pair;
use App\Infrastructure\Utils\Ds\Node;
use Ds\Set;
use Ds\Stack;

class LL extends NonDeterministicParser
{
    public static function fromData(array $data) : LL
    {
        $lexer = new Lexer($data['content'], $data['token_types']);
        $parser = new LL($lexer, $data['grammar']);
        $parser->setInputIndex($data['input_index']);
        $parser->setStackFromData($data['stack']);
        $parser->setParseTreeFromData($data['parse_tree']);

        return $parser;
    }

    public function predict(NonTerminal $lhs, $rhs)
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
        $node = $this->parseTree['stack']->pop();

        for ($i = count($rhs) - 1; $i >= 0; $i--) {
            if (Terminal::isEpsilonStruct($rhs[$i])) {
                continue;
            }

            $this->stack->push($rhs[$i]);

            $childNode = new Node(new Pair(++$this->parseTree['node_index'], $rhs[$i]));
            $node->addChild($childNode);
            $this->parseTree['stack']->push($childNode);
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

        //$inputTerminal = new Terminal($this->input->read()->getType());
        $inputTerminal = $this->input->read()->toTerminal();

        if (!$this->stack->peek()->isTerminal()) {
            throw new ParserException('Could not match a terminal with a non-terminal.');
        }

        if (!$this->stack->peek()->equals($inputTerminal)) {
            throw new ParserException('Expecting ' . $this->stack->peek()->getName() . '; found ' . $inputTerminal->getName() . ' in the input instead.');
        }

        $this->stack->pop();
        $this->input->advance();

        $this->parseTree['stack']->pop();
    }
}
