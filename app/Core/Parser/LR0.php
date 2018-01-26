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

    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        parent::__construct($lexer, $grammar);
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

    public function parse() : bool
    {
        $visitedStates = new Set();
        $startSymbol = $this->grammar->getStartSymbol();

        while (true) {
            $state     = $this->stack->peek();
            $stateData = $state->getData()->toArray();
            $input     = $this->input->hasFinished() ? null : $this->input->read()->getType()->name;

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
                    $this->stack->push($state->getConnectedStates($input)[0]);
                    $this->input->advance();
                    break;
                }

                // Reduce
                try {
                    $nonTerminal   = $lrItem->getLhs()->getName();
                    $count         = count($lrItem->getRhs());
                    $boundaryState = stackPeek($this->stack, $count);

                    if ($dotAtTheEnd && $boundaryState->hasTransition($nonTerminal)) {
                        stackPop($this->stack, $count);
                        $this->stack->push($boundaryState->getConnectedStates($nonTerminal)[0]);
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
