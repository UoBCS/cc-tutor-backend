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
use App\Infrastructure\Utils\Ds\Pair;
use App\Infrastructure\Utils\Ds\Node;
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
    private $parseTree = [
        'root'       => null,
        'stack'      => null,
        'node_index' => 0
    ];

    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        $this->stack = new Stack();
        $this->lexer = $lexer;
        $this->input = $lexer === null ? new ConsumableInput() : new ConsumableInput($this->lexer->getTokens());
        $this->grammar = new Grammar();

        // Setup grammar object
        if ($lexer !== null && count($grammar) > 0) {
            $terminals = array_map(function (TokenType $tokenType) {
                return new Terminal($tokenType);
            }, $this->lexer->getTokenTypes()->toArray());
            $terminals[] = new Terminal(); // epsilon

            $this->grammar->setTerminals(new Set($terminals));
            $this->grammar->setFromData($grammar);

            $this->stack->push($this->grammar->getStartSymbol());
            $this->parseTree['root'] = new Node(
                new Pair($this->parseTree['node_index'], $this->grammar->getStartSymbol())
            );
            $this->parseTree['stack'] = new Stack([$this->parseTree['root']]);
        }
    }

    public static function fromData(array $data) : LL
    {
        $lexer = new Lexer(new InputStream($data['content']), TokenType::fromDataArray($data['token_types']));
        $parser = new LL($lexer, $data['grammar']);
        $parser->setInputIndex($data['input_index']);
        $parser->setStackFromData($data['stack']);
        $parser->setParseTreeFromData($data['parse_tree']);

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
        if ($data === null) {
            return;
        }

        $this->stack = new Stack();
        $tokenTypes = $this->lexer->getTokenTypes();

        for ($i = count($data) - 1; $i >= 0; $i--) {
            $gEntity = $data[$i];

            if (isset($gEntity['regex'])) {
                $tokenType = $this->lexer->getTokenTypeByName($gEntity['name']);

                if ($tokenType !== null) {
                    $this->stack->push(new Terminal($tokenType));
                }
            } else {
                $this->stack->push(new NonTerminal($gEntity));
            }
        }
    }

    public function getGrammar() : Grammar
    {
        return $this->grammar;
    }

    public function getParseTree() : array
    {
        return $this->parseTree;
    }

    public function setParseTreeFromData($data)
    {
        // Recover node index
        $this->parseTree['node_index'] = $data['node_index'];

        // Recover parse tree
        if ($data['tree'] !== null) {
            $root = new Node();
            $this->buildParseTree($root, $data['tree']);
            $this->parseTree['root'] = $root;
        }

        // Recover stack
        if ($data['stack'] !== null) {
            $this->parseTree['stack'] = new Stack();

            for ($i = count($data['stack']) - 1; $i >= 0; $i--) {
                $nodeData = $data['stack'][$i];
                $visitor = new ParseTreeSearchVisitor(new Pair(
                    intval($nodeData[0]),
                    $this->grammar->getGrammarEntityByName(isset($nodeData[1]['name']) ? $nodeData[1]['name'] : $nodeData[1])
                ));
                $stackNode = $root->accept($visitor);

                if ($stackNode === null) {
                    throw new ParserException('Could not load parse tree from the given data.');
                }

                $this->parseTree['stack']->push($stackNode);
            }
        }
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
        $node = $this->parseTree['stack']->pop();

        for ($i = $rhs->count() - 1; $i >= 0; $i--) {
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

        $inputTerminal = new Terminal($this->input->read()->getType());

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

    public function jsonSerialize()
    {
        $visitor = new ParseTreeSerializeVisitor();

        return [
            'stack'      => $this->stack,
            'input'      => $this->input,
            'grammar'    => $this->grammar,
            'parse_tree' => [
                'tree'  => $this->parseTree['root']->accept($visitor),
                'stack' => $this->parseTree['stack'],
                'node_index' => $this->parseTree['node_index']
            ]
        ];
    }

    private function buildParseTree($root, $data)
    {
        $nodeData = $data['node'];

        $root->setValue(new Pair(
            intval($nodeData[0]),
            $this->grammar->getGrammarEntityByName(isset($nodeData[1]['name']) ? $nodeData[1]['name'] : $nodeData[1])
        ));

        foreach ($data['children'] as $nodeObj) {
            $node = new Node();
            $root->addChild($node);

            $this->buildParseTree($node, $nodeObj);
        }
    }
}
