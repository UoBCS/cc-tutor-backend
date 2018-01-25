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
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Utils\Ds\Node;
use Ds\Map;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;

class LR0 extends DeterministicParser
{
    private $itemsDfa;

    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        parent::__construct($lexer, $grammar);
    }

    public function initializeStack()
    {
        $this->stack = new Stack();
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

    public function parse() : bool
    {
        $this->buildItemsDfa();
        return true;
    }

    private function augmentGrammar()
    {
        $augmentedGrammar = clone $this->grammar;
        $startSymbol = $augmentedGrammar->getStartSymbol();
        $eoi = new Terminal(TokenType::eoi());

        $lhs = new NonTerminal($startSymbol->getName() . "'");
        $rhs = [$startSymbol, $eoi];

        $augmentedGrammar->addTerminal($eoi);
        $augmentedGrammar->addProduction($lhs, $rhs);
        $augmentedGrammar->setStartSymbol($lhs);

        return $augmentedGrammar;
    }

    private function buildItemsDfa()
    {
        $grammar = $this->augmentGrammar();
        $startSymbol = $grammar->getStartSymbol();

        $lrItem = new LRItem(
            $startSymbol,
            $grammar->getProductions($startSymbol)[0]
        );

        $items = LR0Helper::itemClosure($lrItem, $grammar);

        $initialState = new State();
        $initialState->setData($items);

        $visited = [];
        $stack = new Stack([$initialState]);

        while (!$stack->isEmpty()) {
            $sourceState = $stack->pop();
            $symbols = LR0Helper::getTransitions($sourceState);

            foreach ($symbols as $grammarEntity) {
                $destState = new State();
                LR0Helper::setItems($destState, $sourceState, $grammarEntity, $grammar);
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
