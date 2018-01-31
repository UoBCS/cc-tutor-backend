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
use JsonSerializable;

class NonDeterministicParser implements JsonSerializable
{
    protected $stack;
    protected $input;
    protected $lexer;
    protected $grammar;
    protected $parseTree = [
        'root'       => null,
        'stack'      => null,
        'node_index' => 0
    ];

    public function __construct(Lexer $lexer = null, array $grammar = [], $type = 'll')
    {
        $this->stack = new Stack();
        $this->input = $lexer === null ? new ConsumableInput() : new ConsumableInput($lexer->getTokens());
        $this->lexer = $lexer;
        $this->grammar = new Grammar();
        $this->parseTree['stack'] = new Stack();

        // Setup grammar object
        if ($lexer !== null && count($grammar) > 0) {
            $this->grammar->setTerminals($this->lexer->getTerminals());
            $this->grammar->setFromData($grammar);

            if ($type === 'll') {
                $this->stack->push($this->grammar->getStartSymbol());
                $this->parseTree['root'] = new Node(
                    new Pair($this->parseTree['node_index'], $this->grammar->getStartSymbol())
                );
                $this->parseTree['stack']->push($this->parseTree['root']);
            } else {
                $this->parseTree['root'] = [];
            }
        }
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

        for ($i = count($data) - 1; $i >= 0; $i--) {
            $gEntity = $data[$i];

            $this->stack->push($this->grammar->getGrammarEntityByName($gEntity));
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
            $topDown = isset($data['tree']['children']);

            if ($topDown) {
                $root = new Node();
                $this->buildParseTreeTopDown($root, $data['tree']);
            } else {
                $root = $this->buildParseTreeBottomUp($data['tree']);
            }

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

    public function dbJsonSerialize()
    {
        $visitor = new ParseTreeSerializeVisitor();
        $data = $this->jsonSerialize();
        $topDown = $this->parseTree['root'] instanceof Node;

        $bottomUpFn = function ($node) use ($visitor) {
            return $node->accept($visitor);
        };

        $data['parse_tree'] = [
            'tree'       => $topDown
                            ? $this->parseTree['root']->accept($visitor)
                            : array_map($bottomUpFn, $this->parseTree['root']),
            'stack'      => $this->parseTree['stack'],
            'node_index' => $this->parseTree['node_index']
        ];

        return $data;
    }

    public function jsonSerialize()
    {
        $topDown = $this->parseTree['root'] instanceof Node;

        $visitor = new ParseTreeSerializeVisitor(function ($pair) {
            return $pair->getSnd()->getName();
        });

        $bottomUpFn = function ($node) use ($visitor) {
            return $node->accept($visitor);
        };

        return [
            'stack'      => array_map('getGrammarEntityName', $this->stack->toArray()),
            'input'      => $this->input,
            'grammar'    => $this->grammar,
            'parse_tree' => $topDown
                            ? $this->parseTree['root']->accept($visitor)
                            : array_map($bottomUpFn, $this->parseTree['root'])
        ];
    }

    protected function buildParseTreeTopDown($root, $data)
    {
        $nodeData = $data['node'];

        $root->setValue(new Pair(
            intval($nodeData[0]),
            $this->grammar->getGrammarEntityByName(isset($nodeData[1]['name']) ? $nodeData[1]['name'] : $nodeData[1])
        ));

        foreach ($data['children'] as $nodeObj) {
            $node = new Node();
            $root->addChild($node);

            $this->buildParseTreeTopDown($node, $nodeObj);
        }
    }

    protected function buildParseTreeBottomUp($data)
    {
        $result = [];

        for ($i = 0; $i < count($data); $i++) {
            $node = new Node();
            $this->buildParseTreeTopDown($node, $data[$i]);
            $result[] = $node;
        }

        return $result;
    }
}
