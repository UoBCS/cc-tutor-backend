<?php

namespace App\Core\Lexer;

use App\Core\Automata\FiniteAutomaton;
use App\Core\Exceptions\LexerException;
use App\Core\IO\InputStream;
use App\Core\Syntax\Grammar\Terminal;
use App\Core\Syntax\Token\Token;
use App\Core\Syntax\Token\TokenType;
use Ds\Set;
use Ds\Stack;
use Ds\Vector;

class Lexer
{
    private $input;
    private $tokenTypes;
    private $tokenLine = 1;
    private $tokenColumn = 0;
    private $lastChar;
    private $currentToken;
    private $dfa;

    private $inspector;

    public function __construct($input, $tokenTypes, $dfaMinimized = false)
    {
        $this->input      = $input instanceof InputStream ? $input : new InputStream($input);
        $this->tokenTypes = $tokenTypes instanceof Set ? $tokenTypes : TokenType::fromDataArray($tokenTypes);
        $this->buildDfa($dfaMinimized);

        $this->inspector = inspector();
        $this->inspector->createStore('breakpoints', 'array');
    }

    public function getTokenTypes()
    {
        return $this->tokenTypes;
    }

    public function getTokenTypeByName(string $name)
    {
        foreach ($this->tokenTypes as $tokenType) {
            if ($tokenType->name === $name) {
                return $tokenType;
            }
        }

        return null;
    }

    public function getTerminals() : Set
    {
        $terminals = array_map(function (TokenType $tokenType) {
            return new Terminal($tokenType);
        }, $this->tokenTypes->toArray());
        $terminals[] = Terminal::epsilon();
        $terminals[] = new Terminal(TokenType::eoi());

        return new Set($terminals);
    }

    public function nextToken(bool $skipF = true): Token
    {
        do {
            if ($this->input === NULL) {
                throw new LexerException('The nextToken method requires a non-null input stream.');
            }

            // Check if EOF has been hit
            if ($this->lastChar == InputStream::EOF) {
                return $this->currentToken = Token::eof();
            }

            $this->currentToken = new Token();
            $text = '';
            $tokenStartColumn = $this->tokenColumn;

            $finalStates = new Stack();
            $s = $this->dfa->getInitial();
            $S = new Stack([$s]);

            /* > */ $this->inspector->breakpoint('init', [
            /* > */    'state'      => $s,
            /* > */    'token_text' => $text
            /* > */ ]);

            while (!$S->isEmpty()) {
                $s = $S->pop();

                /* > */ $this->inspector->breakpoint('consider_state', [
                /* > */    'state' => $s,
                /* > */    'stack' => $S->toArray()
                /* > */ ]);

                if ($s->isFinal()) {
                    $finalStateData = [
                        'state' => $s,
                        'text' => $text,
                        'line' => $this->tokenLine,
                        'column' => $this->tokenColumn,
                        'index' => $this->input->index()
                    ];
                    $finalStates->push($finalStateData);
                }

                /* > */ $this->inspector->breakpoint('consume_char', [
                /* > */     'index' => $this->input->index()
                /* > */ ]);

                $this->consumeChar();

                if ($this->lastChar === InputStream::EOF) {
                    break;
                }

                $neighbours = $s->getState($this->lastChar);

                /* > */ $this->inspector->breakpoint('state_neighbours', [
                /* > */    'neighbours' => $neighbours
                /* > */ ]);

                if (count($neighbours) !== 0) {
                    $text .= $this->lastChar;
                    $S->push($neighbours[0]);

                    /* > */ $this->inspector->breakpoint('update_text', [
                    /* > */    'stack'      => $S->toArray(),
                    /* > */    'token_text' => $text
                    /* > */ ]);
                }
            }

            if ($finalStates->isEmpty()) {
                throw new LexerException('Could not find token');
            }

            // Get longest match
            $chosenState = $finalStates->pop();

            // Update tokenLine tokenColumn and seek head
            $this->tokenLine = $chosenState['line'];
            $this->tokenColumn = $chosenState['column'];
            $this->input->seek($chosenState['index']);
            $this->lastChar = $this->input->LA(1);

            $data = $chosenState['state']->getData();

            if (is_array($data)) {
                // Get highest priority token
                $tokenType = $data[0];

                for ($i = 1; $i < count($data); $i++) {
                    if ($data[$i]->priority < $tokenType->priority) {
                        $tokenType = $data[$i];
                    }
                }

                /* > */ $this->inspector->breakpoint('highest_priority_lexeme', [
                /* > */    'state'      => $data,
                /* > */    'token_type' => $tokenType,
                /* > */    'content_index' => $this->input->index()
                /* > */ ]);

                $this->currentToken->setType($tokenType);
                $this->currentToken->setColumn($tokenStartColumn);
                $this->currentToken->setLine($this->tokenLine);
                $this->currentToken->setText($chosenState['text']);
            } else {
                // throw exception
            }
        } while ($skipF && $this->currentToken->getType()->skippable);

        /* > */ $this->inspector->breakpoint('produced_token', [
        /* > */    'token' => $this->currentToken
        /* > */ ]);

        return $this->currentToken;
    }

    public function getCurrentToken()
    {
        return $this->currentToken;
    }

    public function getInputStream()
    {
        return $this->input;
    }

    public function buildDfa($dfaMinimized)
    {
        // Construct NFAs for regexps
        $nfas = [];

        foreach ($this->tokenTypes as $tokenType) {
            $nfas[] = FiniteAutomaton::fromRegex($tokenType)['nfa'];
        }

        // Combine NFAs to single NFA
        $nfa = FiniteAutomaton::combine($nfas);

        // NFA to DFA
        $this->dfa = $nfa->toDfa();

        if ($dfaMinimized) {
            $this->dfa->minimizeDfa();
        }

        $this->dfa->traverse(function ($s) {
            $s->serialization['showStates'] = false;
        }, 0, NULL, NULL);
    }

    public function getDfa()
    {
        return $this->dfa;
    }

    public function getTokens()
    {
        $tokens = [];

        while (!$this->nextToken()->isEOF()) {
            $tokens[] = $this->currentToken;
        }

        return $tokens;
    }

    private function consumeChar()
    {
        try {
            $this->lastChar = $this->input->LA(1);
            $this->input->consume();
            $this->tokenColumn++;

            if ($this->lastChar === '\r' || $this->lastChar === '\n') {
                $this->tokenColumn = 0;
                $this->tokenLine++;

                if ($this->lastChar == '\r') {
                    $this->lastChar = $this->input->LA(1);

                    if ($this->lastChar !== '\n') {
                        $this->input->seek($this->input->index() - 1);
                    }
                }

                $this->lastChar = '\n';
            }
        } catch (\Exception $e) {
            $this->lastChar = InputStream::EOF;
        }
    }
}
