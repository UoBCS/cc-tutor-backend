<?php

namespace App\Core\Lexer;

use App\Core\Automata\FiniteAutomaton;
use App\Core\Exceptions\LexerException;
use App\Core\IO\InputStream;
use App\Core\Syntax\Token\Token;
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

    public function __construct(InputStream $input, Set $tokenTypes)
    {
        $this->input = $input;
        $this->tokenTypes = $tokenTypes;
        $this->buildDfa();
    }

    public function getTokenTypes()
    {
        return $this->tokenTypes;
    }

    public function nextToken(bool $skipF = true): Token
    {
        do {
            if ($this->input === NULL) {
                throw new LexerException("nextToken requires a non-null input stream.");
            }

            // Check if EOF has been hit
            if ($this->lastChar == InputStream::EOF) {
                return $this->currentToken = Token::eof();
            }

            $this->currentToken = new Token();
            $text = '';
            $tokenStartColumn = $this->tokenColumn;

            $finalStates = new Stack();
            $S = new Stack();
            $S->push($this->dfa->getInitial());

            while (!$S->isEmpty()) {
                $s = $S->pop();

                if ($s->isFinal()) {
                    $finalStates->push([
                        'state' => $s,
                        'text' => $text,
                        'line' => $this->tokenLine,
                        'column' => $this->tokenColumn,
                        'index' => $this->input->index()
                    ]);
                }

                $this->consumeChar();

                if ($this->lastChar === InputStream::EOF) {
                    break;
                }

                $neighbours = $s->getState($this->lastChar);

                if (count($neighbours) !== 0) {
                    $text .= $this->lastChar;
                    $S->push($neighbours[0]);
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

                $this->currentToken->setType($tokenType);
                $this->currentToken->setColumn($tokenStartColumn);
                $this->currentToken->setLine($this->tokenLine);
                $this->currentToken->setText($chosenState['text']);
            } else {
                // throw exception
            }
        } while ($skipF && $this->currentToken->getType()->skippable);

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

    public function buildDfa()
    {
        //JavaTokenType[] tokens = JavaTokenType.values();

        // Construct NFAs for regexps
        $nfas = [];

        foreach ($this->tokenTypes as $tokenType) {
            $nfas[] = FiniteAutomaton::fromRegex($tokenType)['nfa'];
        }

        // Combine NFAs to single NFA
        $nfa = FiniteAutomaton::combine($nfas);

        // NFA to DFA
        $this->dfa = $nfa->toDfa();
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
