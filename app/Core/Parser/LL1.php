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

class LL1
{
    private $stack;
    private $input;
    private $lexer;
    private $grammar;
    private $parseTree = [
        'root'       => null,
        'stack'      => null
    ];

    private $parsingTable;

    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        $this->stack = new Stack();
        $this->lexer = $lexer;
        $this->input = $lexer === null ? new ConsumableInput() : new ConsumableInput($this->lexer->getTokens());
        $this->grammar = new Grammar();

        if ($lexer !== null && count($grammar) > 0) {
            // Prepare terminals
            $terminals = array_map(function (TokenType $tokenType) {
                return new Terminal($tokenType);
            }, $this->lexer->getTokenTypes()->toArray());
            $terminals[] = new Terminal();

            $this->grammar->setTerminals(new Set($terminals));

            // Setup grammar object
            $this->grammar->setFromData($grammar);

            // Prepare stack and parse tree
            $this->stack->push($this->grammar->getStartSymbol());
            $this->parseTree['root'] = new Node($this->grammar->getStartSymbol());
            $this->parseTree['stack'] = new Stack([$this->parseTree['root']]);

            // Compute parsing table for non-interactive mode
            $this->computeParsingTable();
        }
    }

    public function parse()
    {
        while (true) {
            if ($this->stack->isEmpty()) {
                break;
            }

            $grammarEntity = $this->stack->peek();

            if ($grammarEntity->isTerminal()) {
                if ($this->attemptMatch($grammarEntity)) {
                    break;
                }

                //var_dump("MATCH=============");
                //var_dump($this->stack);
            } else {
                if ($this->attemptPredict($grammarEntity)) {
                    break;
                }

                //var_dump("PREDICT=============");
                //var_dump($this->stack);
            }
        }

        // TODO: check for input?

        $visitor = new ParseTreeSerializeVisitor();
        return $this->parseTree['root']->accept($visitor);
    }

    public function computeParsingTable()
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

                $firstSet = $firstSet->merge(
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

    public function firstAll(array $alpha) : Set
    {
        $firstSet = new Set();
        $stopped = false;

        foreach ($alpha as $X_i) {
            $first = $this->first($X_i);
            $firstSet = $firstSet->merge(
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

        return $firstSet;
    }

    public function follow(NonTerminal $A) : Set
    {
        $followSet = new Set();

        // TODO: check this
        if ($A->equals($this->grammar->getStartSymbol())) {
            $followSet->add($this->grammar->getEndOfInputTerminal());
        }

        $productions = $this->productionsWhereNonTerminalInRhs($A);
        //var_dump('LOOOOOOOOOOOOOOOOOOOOOOOOOOL');
        //var_dump($A);
        //var_dump($productions);

        foreach ($productions as $X => $rhss) {
            foreach ($rhss as $rhs) {
                $index = arrayFind($rhs, $A); //$rhs->find($A);
                //var_dump($index);

                if ($index === count($rhs) - 1 && !$X->equals($A)) {
                    $followSet = $followSet->merge($this->follow($X));
                } else {
                    $beta = array_slice($rhs, $index + 1);
                    $firstBeta = $this->firstAll($beta);
                    //var_dump($firstBeta);

                    $followSet = $followSet->merge(
                        $firstBeta->filter(function ($terminal) {
                            return !$terminal->isEpsilon();
                        })
                    );

                    if ($firstBeta->contains(Terminal::epsilon()) && !$X->equals($A)) {
                        $followSet = $followSet->merge($this->follow($X));
                    }
                }
            }
        }

        return $followSet;
    }

    private function productionsWhereNonTerminalInRhs(NonTerminal $A)
    {
        $map = new Map();

        foreach ($this->grammar->getAllProductions() as $lhs => $rhss) {
            $map->put($lhs, []);

            foreach ($rhss as $rhs) {
                foreach ($rhs as $grammarEntity) {
                    if ($grammarEntity->equals($A)) {
                        $updatedRhs = $map->get($lhs); //, $rhs);
                        $updatedRhs[] = $rhs;
                        $map->put($lhs, $updatedRhs);
                    }
                }
                /*if ($rhs->contains($A)) {
                    $map->get($lhs)->push($rhs);
                }*/
            }
        }

        /*var_dump('============================================');
        var_dump($map);
        var_dump('============================================');*/

        $map = $map->filter(function ($lhs, $rhss) {
            return count($rhss) > 0;
        });

        return $map;
    }

    private function attemptMatch(Terminal $stackTerminal)
    {
        if ($this->input->hasFinished()) {
            if ($this->stack->isEmpty()) {
                return true;
            } else {
                throw new ParserException('Premature end of input.');
            }
        }

        $inputTerminal = $this->input->read()->toTerminal();

        if (!$stackTerminal->equals($inputTerminal)) {
            throw new ParserException('Expecting ' . $stackTerminal->getName() . '; found ' . $inputTerminal->getName() . ' in the input instead.');
        }

        $this->stack->pop();
        $this->input->advance();
        $this->parseTree['stack']->pop();

        return false;
    }

    private function attemptPredict(NonTerminal $stackNonTerminal)
    {
        if ($this->stack->isEmpty()) {
            if ($this->input->hasFinished()) {
                return true;
            } else {
                $tokens = array_map(function ($token) {
                    return $token->getType()->name;
                }, $this->input->getRemaining());

                $tokensStr = implode(' ', $tokens);

                throw new ParserException("Unexpected input $tokensStr at the end.");
            }
        }

        $rhss = $this->grammar->getProductions($stackNonTerminal);
        $firstInputTerminal = $this->input->read()->toTerminal();

        $firstMatches = [];
        $followMatches = [];
        foreach ($rhss as $alpha) {
            $firstAlpha = $this->firstAll($alpha);
            //var_dump($firstAlpha);

            if ($firstAlpha->contains($firstInputTerminal)) {
                $firstMatches[] = $alpha;
            }

            if ($firstAlpha->contains(Terminal::epsilon())) {
                if ($this->follow($stackNonTerminal)->contains($firstInputTerminal)) {
                    $followMatches[] = $alpha;
                }
            }
        }

        // Check FIRST-FIRST conflicts
        if (count($firstMatches) > 1) {
            throw new ParserException('Encountered FIRST-FIRST conflict.');
        }

        // Check FIRST-FOLLOW conflicts
        if (count($firstMatches) === 1 && count($followMatches) === 1) {
            throw new ParserException('Encountered FIRST-FOLLOW conflict.');
        }

        $this->stack->pop();
        $node = $this->parseTree['stack']->pop();

        $matches = array_merge($firstMatches, $followMatches);

        //var_dump($firstMatches);
        //var_dump($followMatches);
        //var_dump($matches);

        //var_dump($this->stack);
        //die();

        $rhs = $matches[0];
        for ($i = count($rhs) - 1; $i >= 0; $i--) {
            $this->stack->push($rhs[$i]);

            $childNode = new Node($rhs[$i]);
            $node->addChild($childNode);
            $this->parseTree['stack']->push($childNode);
        }

        return false;
    }
}
