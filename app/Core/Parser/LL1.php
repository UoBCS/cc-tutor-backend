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
use App\Infrastructure\Utils\Ds\Pair;
use App\Infrastructure\Utils\Ds\Node;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;
use JsonSerializable;

class LL1 implements JsonSerializable
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
            $terminals[] = new Terminal();

            $this->grammar->setTerminals(new Set($terminals));
            $this->grammar->setFromData($grammar);

            $this->stack->push($this->grammar->getStartSymbol());
            $this->parseTree['root'] = new Node(
                new Pair($this->parseTree['node_index'], $this->grammar->getStartSymbol())
            );
            $this->parseTree['stack'] = new Stack([$this->parseTree['root']]);
        }
    }

    public function parse()
    {

    }

    public function first(GrammarEntity $X) : Set
    {
        $firstSet = new Set();

        if ($X->isTerminal()) {
            $firstSet->add($X);
            return $firstSet;
        }

        if ($this->grammar->hasProduction($X, Terminal::epsilon())) {
            $firstSet->add(Terminal::epsilon());
        }

        $productions = $this->grammar->getProductions($X);
        foreach ($productions as $production) {
            $stopped = false;

            foreach ($production as $Y_i) {
                $first = $this->first($Y_i);

                $firstSet = $firstSet->mergeAll(
                    $first->filter(function ($terminal) {
                        return !$terminal->isEpsilon();
                    })
                );

                if (!$first->contains(Terminal::epsilon())) {
                    $stopped = true;
                    break;
                }
            }

            if (!$stopped) {
                $firstSet->add(Terminal::epsilon());
            }
        }

        return $firstSet;
    }

    public function firstAll()
    {

    }

    public function follow(NonTerminal $A) : Set
    {
        $followSet = new Set();

        /*Set<Terminal> followSet = new HashSet<>();

        // First put $ (the end of input marker) in Follow(S) (S is the start symbol)
        if (nt.equals(grammar.getStartSymbol())) {
            followSet.add(grammar.getEndOfInputTerminal());
        }

        Map<NonTerminal, List<List<GrammarEntity>>> productions = productionsWhereNonTerminalInRhs();

        // If there is a production A -> aBb, (where a can be a whole string) then everything in FIRST(b) except for ε is placed in FOLLOW(B).


        // If there is a production A -> aB, then everything in FOLLOW(A) is in FOLLOW(B)

        // If there is a production A -> aBb, where FIRST(b) contains ε, then everything in FOLLOW(A) is in FOLLOW(B)

        return followSet;*/

        return $followSet;
    }
}
