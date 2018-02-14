<?php

namespace App\Core\CekMachine\LambdaCalculus;

use Exception;

class LambdaParser
{
    /*
    'term'         => [['application'], ['LAMBDA', 'VAR', 'DOT', 'term']],
    'application'  => [['atom', 'application1']],
    'application1' => [['atom', 'application1'], null],
    'atom'         => [['LPAREN', 'term', 'RPAREN'], ['VAR'], ['CONST']]
    */

    private static $LAMBDA = '\\';
    private $input;

    public function __construct(string $input)
    {
        $this->input = $input;
    }

    public function parse()
    {
        return $this->term();
    }

    private function term()
    {
        if ($this->more() && $this->peek() === self::$LAMBDA) {
            $this->eat(self::$LAMBDA);
            $variable = $this->variable();
            $this->eat('.');
            $body = $this->term();

            return new Func($variable, $body);
        } else {
            return $this->application();
        }
    }

    private function application()
    {
        //(λx.x)1

        $lhs = $this->atom();

        while (true) {
            $rhs = $this->atom();

            if ($rhs === null) {
                return $lhs;
            } else {
                $lhs = new Application($lhs, $rhs);
            }
        }
    }

    private function atom()
    {
        if (!$this->more()) {
            return null;
        }

        if ($this->peek() === '(') {
            $this->eat('(');
            $term = $this->term();
            $this->eat(')');

            return $term;
        }

        if (preg_match("/[a-z]/", $this->peek())) {
            return $this->variable();
        }

        if (preg_match("/[0-9]/", $this->peek())) {
            return $this->constant();
        }

        return null;
    }

    private function variable()
    {
        $name = '';

        while ($this->more() && preg_match("/[a-z]/", $this->peek())) {
            $name .= $this->next();
        }

        return new Variable($name);
    }

    private function constant()
    {
        $value = '';

        while ($this->more() && preg_match("/[0-9]/", $this->peek())) {
            $value .= $this->next();
        }

        return new Constant(intval($value));
    }

    private function peek()
    {
        return $this->input[0];
    }

    private function eat(string $c)
    {
        $char = $this->peek();
        if ($char === $c) {
            $this->input = substr($this->input, 1);
        }
        else {
            throw new Exception("Expected $c; got: $char");
        }
    }

    private function next()
    {
        $c = $this->peek();
        $this->eat($c);
        return $c;
    }

    private function more()
    {
        return strlen($this->input) > 0;
    }
}
