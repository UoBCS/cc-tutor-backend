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

class LR extends NonDeterministicParser
{
    public static function fromData(array $data) : LR
    {
        $lexer = new Lexer($data['content'], $data['token_types']);
        $parser = new LR($lexer, $data['grammar'], 'lr');
        $parser->setInputIndex($data['input_index']);
        $parser->setStackFromData($data['stack']);
        $parser->setParseTreeFromData($data['parse_tree']);

        return $parser;
    }

    public function reduce(NonTerminal $lhs, $rhs)
    {
        if ($this->stack->isEmpty()) {
            throw new ParserException('Could not reduce, the stack is empty.');
        }

        $stackArr = array_reverse($this->stack->toArray());
        $grammarEntity = $this->stack->peek();
        $stackCount = count($stackArr);
        if ($stackCount === 1 &&
            $this->grammar->getStartSymbol()->equals($grammarEntity)) {
            // success
        }

        if (!$this->grammar->hasProduction($lhs, $rhs)) {
            throw new ParserException('The supplied production has not been found.');
        }

        if (count($rhs) !== $stackCount) {
            throw new ParserException('The right hand side of the supplied production was not found on top of the stack.');
        }

        for ($i = 0; $i < $stackCount; $i++) {
            if (!$rhs[$i]->equals($stackArr[$i])) {
                throw new ParserException('The right hand side of the supplied production was not found on top of the stack.');
            }
        }

        for ($i = 0; $i < $stackCount; $i++) {
            $this->stack->pop();
            $nodes[] = array_pop($this->parseTree['root']);
        }

        $this->stack->push($lhs);
        $parentNode = new Node(new Pair(++$this->parseTree['node_index'], $lhs));

        for ($i = 0; $i < count($nodes); $i++) {
            $parentNode->addChild($nodes[$i]);
        }

        $this->parseTree['root'][] = $parentNode;
    }

    public function shift()
    {
        if ($this->input->hasFinished()) {
            $grammarEntity = $this->stack->peek();
            $stackCount = count($this->stack->toArray());
            if ($stackCount === 1 &&
                $this->grammar->getStartSymbol()->equals($grammarEntity)) {
                // success
            } else {
                throw new ParserException('Error processing.');
            }
        }

        $inputTerminal = $this->input->read()->toTerminal();

        $this->stack->push($inputTerminal);
        $this->input->advance();

        $node = new Node(new Pair(++$this->parseTree['node_index'], $inputTerminal));
        $this->parseTree['root'][] = $node;
    }
}
