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
use App\Core\Syntax\Token\Token;
use App\Core\Syntax\Token\TokenType;
use App\Infrastructure\Utils\Ds\Node;
use Ds\Map;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;

class LL1 extends DeterministicParser
{
    public function __construct(Lexer $lexer = null, array $grammar = [])
    {
        parent::__construct($lexer, $grammar);
    }

    public function initialize()
    {
        $this->input->add(Token::eof());
    }

    public function initializeStack()
    {
        $this->stack->push($this->grammar->getStartSymbol());
    }

    public function initializeParseTree()
    {
        $this->parseTree['root'] = new Node($this->grammar->getStartSymbol());
        $this->parseTree['stack'] = new Stack([$this->parseTree['root']]);
    }

    public function getJsonParseTree() : array
    {
        $visitor = new ParseTreeSerializeVisitor(function ($node) {
            return $node->getName();
        });
        return $this->parseTree['root']->accept($visitor);
    }

    public function parse() : bool
    {
        while (true) {
            if ($this->stack->isEmpty()) {
                break;
            }

            $grammarEntity = $this->stack->peek();
            $result = null;

            /* > */ $this->inspector->breakpoint('pre_step', [
            /* > */     'stack'       => array_map('getGrammarEntityName', $this->stack->toArray()),
            /* > */     'input_index' => $this->input->getIndex(),
            /* > */     'parse_tree'  => $this->getJsonParseTree()
            /* > */ ]);

            if ($grammarEntity->isTerminal()) {
                $result = $this->attemptMatch($grammarEntity);
            } else {
                /* > */ $this->inspector->breakpoint('init_predict', null);
                $result = $this->attemptPredict($grammarEntity);
            }

            if ($result['status'] === 'DONE' || $result['status'] === 'ERROR') {
                break;
            }
        }

        if ($result['status'] === 'ERROR') {
            /* > */ $this->inspector->breakpoint('parse_error', [
            /* > */     'message' => $result['message']
            /* > */ ]);

            return false;
        }

        /* > */ $this->inspector->breakpoint('parse_end', [
        /* > */     'parse_tree'  => $this->getJsonParseTree(),
        /* > */     'stack'       => array_map('getGrammarEntityName', $this->stack->toArray()),
        /* > */     'input_index' => $this->input->getIndex(),
        /* > */ ]);

        return true;
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

        if ($A->equals($this->grammar->getStartSymbol())) {
            $followSet->add($this->grammar->getEndOfInputTerminal());
        }

        $productions = $this->productionsWhereNonTerminalInRhs($A);

        foreach ($productions as $X => $rhss) {
            foreach ($rhss as $rhs) {
                $index = arrayFind($rhs, $A);

                if ($index === count($rhs) - 1 && !$X->equals($A)) {
                    $followSet = $followSet->merge($this->follow($X));
                } else {
                    $beta = array_slice($rhs, $index + 1);
                    $firstBeta = $this->firstAll($beta);

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

    private function productionsWhereNonTerminalInRhs(NonTerminal $A) : Map
    {
        $map = new Map();

        foreach ($this->grammar->getAllProductions() as $lhs => $rhss) {
            $map->put($lhs, []);

            foreach ($rhss as $rhs) {
                foreach ($rhs as $grammarEntity) {
                    if ($grammarEntity->equals($A)) {
                        $updatedRhs = $map->get($lhs);
                        $updatedRhs[] = $rhs;
                        $map->put($lhs, $updatedRhs);
                    }
                }
            }
        }

        $map = $map->filter(function ($lhs, $rhss) {
            return count($rhss) > 0;
        });

        return $map;
    }

    private function attemptMatch(Terminal $stackTerminal) : array
    {
        if ($this->input->hasFinished()) {
            if ($this->stack->isEmpty()) {
                return [
                    'status' => 'DONE'
                ];
            } else {
                return [
                    'status'  => 'ERROR',
                    'message' => 'Premature end of input.'
                ];
            }
        }

        $inputTerminal = $this->input->read()->toTerminal();

        if (!$stackTerminal->equals($inputTerminal)) {
            return [
                'status'  => 'ERROR',
                'message' => 'Expecting ' . $stackTerminal->getName() . '; found ' . $inputTerminal->getName() . ' in the input instead.'
            ];
        }

        /* > */ $this->inspector->breakpoint('match_input_index', [
        /* > */     'input_index' => $this->input->getIndex()
        /* > */ ]);

        $this->stack->pop();
        $this->input->advance();
        $this->parseTree['stack']->pop();

        return [
            'status' => 'CONTINUE'
        ];
    }

    private function attemptPredict(NonTerminal $stackNonTerminal) : array
    {
        if ($this->stack->isEmpty()) {
            if ($this->input->hasFinished()) {
                return [
                    'status' => 'DONE'
                ];
            } else {
                $tokens = array_map(function ($token) {
                    return $token->getType()->name;
                }, $this->input->getRemaining());

                $tokensStr = implode(' ', $tokens);

                return [
                    'status'  => 'ERROR',
                    'message' => "Unexpected input $tokensStr at the end."
                ];
            }
        }

        $rhss = $this->grammar->getProductions($stackNonTerminal);
        $firstInputTerminal = $this->input->read()->toTerminal();

        $firstMatches = [];
        $followMatches = [];
        foreach ($rhss as $alpha) {
            $firstAlpha = $this->firstAll($alpha);

            if ($firstAlpha->contains($firstInputTerminal)) {
                /* > */ $this->inspector->breakpoint('first', [
                /* > */     'alpha'          => array_map('getGrammarEntityName', $alpha),
                /* > */     'first_set'      => array_map('getGrammarEntityName', $firstAlpha->toArray()),
                /* > */     'input_terminal' => $firstInputTerminal->getName()
                /* > */ ]);

                $firstMatches[] = $alpha;
            }

            if ($firstAlpha->contains(Terminal::epsilon())) {
                $followSet = $this->follow($stackNonTerminal);

                if ($followSet->contains($firstInputTerminal)) {
                    /* > */ $this->inspector->breakpoint('follow', [
                    /* > */     'non_terminal'   => $stackNonTerminal->getName(),
                    /* > */     'first_set'      => array_map('getGrammarEntityName', $firstAlpha->toArray()),
                    /* > */     'follow_set'     => array_map('getGrammarEntityName', $followSet->toArray()),
                    /* > */     'input_terminal' => $firstInputTerminal->getName()
                    /* > */ ]);

                    $followMatches[] = $alpha;
                }
            }
        }

        // Check FIRST-FIRST conflicts
        if (count($firstMatches) > 1) {
            return [
                'status'  => 'ERROR',
                'message' => 'Encountered FIRST-FIRST conflict.'
            ];
        }

        // Check FIRST-FOLLOW conflicts
        if (count($firstMatches) === 1 && count($followMatches) === 1) {
            return [
                'status'  => 'ERROR',
                'message' => 'Encountered FIRST-FOLLOW conflict.'
            ];
        }

        $this->stack->pop();
        $node = $this->parseTree['stack']->pop();

        $matches = array_merge($firstMatches, $followMatches);
        $rhs = $matches[0];

        /* > */ $this->inspector->breakpoint('predict_chosen_production', [
        /* > */     'production' => [$stackNonTerminal, array_map('getGrammarEntityName', $rhs)],
        /* > */     'type'       => count($firstMatches) > 0 ? 'first' : 'follow'
        /* > */ ]);

        for ($i = count($rhs) - 1; $i >= 0; $i--) {
            $this->stack->push($rhs[$i]);

            $childNode = new Node($rhs[$i]);
            $node->addChild($childNode);
            $this->parseTree['stack']->push($childNode);
        }

        return [
            'status' => 'CONTINUE'
        ];
    }
}
