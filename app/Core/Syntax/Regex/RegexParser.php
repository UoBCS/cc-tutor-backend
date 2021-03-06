<?php

namespace App\Core\Syntax\Regex;

use Exception;

class RegexParser
{
    private $input;

    public function __construct(string $input)
    {
        $this->input = $input;
    }

    public function parse()
    {
        return $this->regex();
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

    private function regex()
    {
        $term = $this->term();

        if ($this->more() && $this->peek() === '|') {
            $this->eat('|');
            $regex = $this->regex();
            return new TreeTypes\Choice($term, $regex);
        } else {
            return $term;
        }
    }

    private function term()
    {
        $factor = null;
        $i = 0;

        while ($this->more() && $this->peek() !== ')' && $this->peek() !== '|') {
            if ($i !== 0) {
                $nextFactor = $this->factor();
                $factor = new TreeTypes\Sequence($factor, $nextFactor);
            } else {
                $factor = $this->factor();
            }

            $i++;
        }

        return $factor;
    }

    private function factor()
    {
        $base = $this->base();

        while ($this->more() && ($this->peek() === '*' || $this->peek() === '+' || $this->peek() === '?')) {
            switch ($this->peek()) {
                case '*':
                    $this->eat('*');
                    $base = new TreeTypes\Repetition($base);
                break;

                case '+':
                    $this->eat('+');
                    $base = new TreeTypes\RepetitionFromOne($base);
                break;

                default:
                    $this->eat('?');
                    $base = new TreeTypes\Optional($base);
                break;
            }
        }

        return $base;
    }

    private function group()
    {
        $groupItems = new TreeTypes\GroupItems();

        while ($this->more() && $this->peek() !== ']') {
            $groupItems->addItem($this->base());
        }

        return $groupItems;
    }

    private function base()
    {
        switch ($this->peek()) {
        case '(':
            $this->eat('(');
            $r = $this->regex();
            $this->eat(')');
            return $r;

        case '\\':
            $this->eat('\\');
            $esc = $this->next();
            //return base();
            return new TreeTypes\Primitive($esc);

        case '.':
            $this->next();
            return new TreeTypes\AnyChar();

        case '[':
            $this->eat('[');
            $gr = $this->group();
            $this->eat(']');
            return $gr;

        default:
            $c = $this->next();

            if ($this->more() && $this->peek() === '-') {
                $this->eat('-');
                return new TreeTypes\Range($c, $this->next());
            } else {
                return new TreeTypes\Primitive($c);
            }
        }
    }
}
