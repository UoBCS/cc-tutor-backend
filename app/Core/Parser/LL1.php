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
        'root'  => null,
        'stack' => null
    ];

    private $parsingTable;

    private $inspector;

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

        $this->inspector = inspector();
        $this->inspector->createStore('breakpoints', 'array');
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
                /* > */ $this->inspector->breakpoint('init_match', null);
                $result = $this->attemptMatch($grammarEntity);
                /* > */ $this->inspector->breakpoint('end_match', null);
            } else {
                /* > */ $this->inspector->breakpoint('init_predict', null);
                $result = $this->attemptPredict($grammarEntity);
                /* > */ $this->inspector->breakpoint('end_predict', null);
            }

            if ($result['status'] === 'DONE' || $result['status'] === 'ERROR') {
                break;
            }
        }

        if ($result['status'] === 'ERROR') {
            /* > */ $this->inspector->breakpoint('parse_error', [
            /* > */     'input_index' => $this->input->getIndex()
            /* > */ ]);

            return false;
        }

        return true;

        // TODO: check for input?
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

        /* > */ $this->inspector->breakpoint('init_first_all', [
        /* > */     'alpha'     => array_map('getGrammarEntityName', $alpha), // TODO: transform to epsilon
        /* > */     'first_set' => array_map('getGrammarEntityName', $firstSet->toArray())
        /* > */ ]);

        foreach ($alpha as $X_i) {
            $first = $this->first($X_i);
            $firstSet = $firstSet->merge(
                $first->filter(function ($terminal) {
                    return !$terminal->isEpsilon();
                })
            );

            /* > */ $this->inspector->breakpoint('first_all_accumulator', [
            /* > */     'grammar_entity' => $X_i->getName(),
            /* > */     'first'          => array_map('getGrammarEntityName', $first->toArray()),
            /* > */     'first_set'      => array_map('getGrammarEntityName', $firstSet->toArray())
            /* > */ ]);

            if (!$first->contains(Terminal::epsilon())) {
                /* > */ $this->inspector->breakpoint('first_no_epsilon', null);
                $stopped = true;
                break;
            }
        }

        if (!$stopped) {
            $firstSet->add(Terminal::epsilon());

            /* > */ $this->inspector->breakpoint('first_set_add_epsilon', [
            /* > */     'first_set' => array_map('getGrammarEntityName', $firstSet->toArray())
            /* > */ ]);
        } else {
            /* > */ $this->inspector->breakpoint('end_first_all', [
            /* > */     'first_set' => array_map('getGrammarEntityName', $firstSet->toArray())
            /* > */ ]);
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

    public function getInput() : ConsumableInput
    {
        return $this->input;
    }

    public function getJsonParseTree() : array
    {
        $visitor = new ParseTreeSerializeVisitor(function ($node) {
            return $node->getName();
        });
        return $this->parseTree['root']->accept($visitor);
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
                $firstMatches[] = $alpha;
            }

            if ($firstAlpha->contains(Terminal::epsilon())) {
                if ($this->follow($stackNonTerminal)->contains($firstInputTerminal)) {
                    $followMatches[] = $alpha;
                }
            }
        }

        // TODO: stringify matches when conflict happens
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
        /* > */     'production' => [$stackNonTerminal, array_map('getGrammarEntityName', $rhs)]
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
