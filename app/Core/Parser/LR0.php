<?php

namespace App\Core\Parser;

use App\Core\Automata\FiniteAutomaton;
use App\Core\Automata\State;
use App\Core\Exceptions\ParserException;
use App\Core\IO\InputStream;
use App\Core\IO\ConsumableInput;
use App\Core\Lexer\Lexer;
use App\Core\Syntax\Grammar\Grammar;
use App\Core\Syntax\Grammar\GrammarEntity;
use App\Core\Syntax\Grammar\NonTerminal;
use App\Core\Syntax\Grammar\Terminal;
use App\Core\Syntax\Token\Token;
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Utils\Ds\Node;
use Ds\Map;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;
use Exception;

class LR0 extends DeterministicParser
{
    private $itemsDfa;
    private $parseTreeTraversalFn;

    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        parent::__construct($lexer, $grammar);

        $this->parseTreeTraversalFn = function ($node) {
            return $node->accept(new ParseTreeSerializeVisitor());
        };
    }

    public function initialize()
    {
        $this->buildItemsDfa();
        $this->input->add(Token::eof());
    }

    public function initializeStack()
    {
        $this->stack = new Stack([$this->itemsDfa->getInitial()]);
    }

    public function initializeParseTree()
    {
        $this->parseTree = [
            'root' => []
        ];
    }

    public function getItemsDfa() : FiniteAutomaton
    {
        return $this->itemsDfa;
    }

    public function getJsonItemsDfa() : array
    {
        $data = [];

        $fn1 = function ($state, $arr) {
            foreach ($state->getData() as $lrItem) {
                $lrItem->setJsonSerializeOptions([
                    'showNamesOnly' => true
                ]);
            }
            $arr[] = $state->jsonSerialize();
            return $arr;
        };

        $data['states'] = $this->itemsDfa->traverse($fn1, [], null, null, 0);

        $fn2 = function ($src, $c, $dest, $arr) {
            $src->setJsonSerializeOptions([
                'showData' => false
            ]);

            $dest->setJsonSerializeOptions([
                'showData' => false
            ]);

            $arr[] = [
                'src'  => $src,
                'char' => $c,
                'dest' => $dest
            ];
            return $arr;
        };

        $data['transitions'] = $this->itemsDfa->traverse(null, null, $fn2, [], 1);

        return $data;
    }

    public function getJsonParseTree() : array
    {
        $visitor = new ParseTreeSerializeVisitor();
        return $this->parseTree['root'][0]->accept($visitor);
    }

    public function parse() : bool
    {
        $visitedStates = new Set();
        $startSymbol = $this->grammar->getStartSymbol();

        /* > */ $this->inspector->breakpoint('global_initialize', [
        /* > */     'stack' => $this->stack->toArray()
        /* > */ ]);

        while (true) {
            $state     = $this->stack->peek();
            $stateData = $state->getData()->toArray();
            $input     = $this->input->hasFinished() ? null : $this->input->read()->getType()->name;

            /* > */ $this->inspector->breakpoint('initialize', [
            /* > */     'state'       => $state,
            /* > */     'input_index' => $input === null ? null : $this->input->getIndex()
            /* > */ ]);

            if ($input === null
            && $stateData[0]->dotIsAtTheEnd()
            && $stateData[0]->getLhs()->equals($startSymbol)) {
                break;
            }

            foreach ($stateData as $lrItem) {

                $dotAtTheEnd = $lrItem->dotIsAtTheEnd();

                // Shift
                if ($input !== null
                && !$dotAtTheEnd
                && $lrItem->getNext()->getName() === $input
                && $state->hasTransition($input)) {
                    $destState = $state->getConnectedStates($input)[0];

                    $this->stack->push($destState);
                    $this->input->advance();
                    $this->parseTree['root'][] = new Node($input);

                    /* > */ $this->inspector->breakpoint('shift', [
                    /* > */     'lr_item'     => $lrItem,
                    /* > */     'transition' => [
                    /* > */         'src' => $state->getId(),
                    /* > */         'char' => $input,
                    /* > */         'dest' => $destState->getId()
                    /* > */     ],
                    /* > */     'input_index' => $input === null ? null : $this->input->getIndex(),
                    /* > */     'stack' => $this->stack->toArray(),
                    /* > */     'parse_tree' => array_map($this->parseTreeTraversalFn, $this->parseTree['root'])
                    /* > */ ]);
                    break;
                }

                // Reduce
                try {
                    $nonTerminal   = $lrItem->getLhs()->getName();
                    $count         = count($lrItem->getRhs());
                    $boundaryState = stackPeek($this->stack, $count);

                    if ($dotAtTheEnd && $boundaryState->hasTransition($nonTerminal)) {
                        $destState = $boundaryState->getConnectedStates($nonTerminal)[0];

                        stackPop($this->stack, $count);
                        $this->stack->push($destState);

                        $nodes = [];
                        for ($i = 0; $i < $count; $i++) {
                            $nodes[] = array_pop($this->parseTree['root']);
                        }

                        $parentNode = new Node($nonTerminal);

                        for ($i = 0; $i < count($nodes); $i++) {
                            $parentNode->addChild($nodes[$i]);
                        }

                        $this->parseTree['root'][] = $parentNode;

                        /* > */ $this->inspector->breakpoint('reduce', [
                        /* > */     'lr_item' => $lrItem,
                        /* > */     'transition' => [
                        /* > */         'src'  => $boundaryState->getId(),
                        /* > */         'char' => $nonTerminal,
                        /* > */         'dest' => $destState->getId()
                        /* > */     ],
                        /* > */     'input_index' => $input === null ? null : $this->input->getIndex(),
                        /* > */     'stack' => $this->stack->toArray(),
                        /* > */     'parse_tree' => array_map($this->parseTreeTraversalFn, $this->parseTree['root'])
                        /* > */ ]);
                        break;
                    }
                } catch (Exception $e) {}

            }
        }

        return true;
    }

    private function augmentGrammar()
    {
        $startSymbol = $this->grammar->getStartSymbol();
        $eoi = new Terminal(TokenType::eoi());

        $lhs = new NonTerminal($startSymbol->getName() . "'");
        $rhs = [$startSymbol, $eoi];

        $this->grammar->addTerminal($eoi);
        $this->grammar->addProduction($lhs, $rhs);
        $this->grammar->setStartSymbol($lhs);
    }

    // TODO: check for conflicts
    private function buildItemsDfa()
    {
        $this->augmentGrammar();
        $startSymbol = $this->grammar->getStartSymbol();

        $lrItem = new LRItem(
            $startSymbol,
            $this->grammar->getProductions($startSymbol)[0]
        );

        $items = LR0Helper::itemClosure($lrItem, $this->grammar);

        $initialState = new State();
        $initialState->setData($items);

        $visited = [];
        $stack = new Stack([$initialState]);

        while (!$stack->isEmpty()) {
            $sourceState = $stack->pop();
            $symbols = LR0Helper::getTransitions($sourceState);

            foreach ($symbols as $grammarEntity) {
                $destState = new State();
                LR0Helper::setItems($destState, $sourceState, $grammarEntity, $this->grammar);
                LR0Helper::setFinal($destState);

                $addToVisited = true;
                foreach ($visited as $state) {
                    if (LR0Helper::equalStates($state, $destState)) {
                        $destState = $state;
                        $addToVisited = false;
                        break;
                    }
                }

                if ($addToVisited) {
                    $visited[] = $destState;
                    $stack->push($destState);
                }

                $sourceState->addTransition($destState, $grammarEntity->getName());
            }
        }

        $this->itemsDfa = new FiniteAutomaton($initialState);
        $this->itemsDfa->setIds();
    }
}
