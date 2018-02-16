<?php

namespace App\Core\CekMachine\LambdaCalculus;

use Ds\Set;
use JsonSerializable;

class Application extends Expression implements JsonSerializable
{
    private $function;
    private $argument;

    public function __construct(Expression $function, Expression $argument)
    {
        $this->function = $function;
        $this->argument = $argument;
    }

    public function reducible() : bool
    {
        return $this->function->reducible()
            || $this->argument->reducible()
            || $this->function instanceof Func;
    }

    public function reduce() : Expression
    {
        if ($this->function->reducible()) {
            return new Application($this->function->reduce(), $this->argument);
        } else if ($this->argument->reducible()) {
            return new Application($this->function, $this->argument->reduce());
        } else if ($this->function instanceof Func) {
            $fun = $this->function;
            return self::subst($fun->getBody(), $fun->getName(), $this->getArgument());
        }

        return $this;
    }

    public function deepReduce() : Expression
    {
        $app = $this->reduce();
        return $app->reducible() ? $app->deepReduce() : $app;
    }

    public function getFunction() : Expression
    {
        return $this->function;
    }

    public function setFunction(Expression $function)
    {
        $this->function = $function;
    }

    public function getArgument() : Expression
    {
        return $this->argument;
    }

    public function setArgument(Expression $argument)
    {
        $this->argument = $argument;
    }

    public function jsonSerialize()
    {
        return [
            'type'     => 'APPL',
            'function' => $this->function,
            'argument' => $this->argument
        ];
    }

    public function __toString()
    {
        $apply = '';
        if ($this->function instanceof Func) {
            $apply .= '(' . strval($this->function) . ')';
        } else {
            $apply .= strval($this->function);
        }

        $apply .= ' ';

        if ($this->argument instanceof Variable) {
            $apply .= strval($this->argument);
        } else {
            $apply .= '(' . strval($this->argument) . ')';
        }

        return $apply;
    }

    public function __clone()
    {
        foreach ($this as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $this->$key;
            } else if (is_array($value)) {
                $newArray = [];
                foreach ($value as $arrayKey => $arrayValue) {
                    $newArray[$arrayKey] = is_object($arrayValue) ? clone $arrayValue : $arrayValue;
                }
                $this->$key = $newArray;
            }
        }
    }

    private static function subst(Expression $expr, Variable $name, Expression $repl) : Expression
    {
        if ($expr instanceof Variable) {
            if ($name->equals($expr)) {
                return $repl;
            } else {
                return $expr;
            }
        } else if ($expr instanceof Application) {
            return new Application(
                    subst($expr->getFunction(), $name, $repl),
                    subst($expr->getArgument(), $name, $repl));
        } else if ($expr instanceof Func) {
            $fvs = self::freeVariables($repl);
            $fvs->add($name);
            $newName2 = uniqueName($expr->getName(), $fvs);
            $newBody = subst($expr->getBody(), $expr->getName(), $newName2);
            return new Func($newName2, subst($newBody, $name, $repl));
        }
        // unexpected operation
        return null;
    }

    private static function uniqueName(Variable $name, Set $usedNames) : Variable
    {
        if ($usedNames->contains($name)) {
            return self::uniqueName(new Variable($name->getName() . "'"), $usedNames);
        } else {
            return $name;
        }
    }

    private static function freeVariables(Expression $expr) : Set
    {
        $vars = null;
        if ($expr instanceof Variable) {
            $vars = new Set();
            $vars->add($expr);
        } else if ($expr instanceof Application) {
            $vars = self::freeVariables($expr->getFunction());
            $vars = $vars->merge(freeVariables($expr->getArgument()));
        } else if ($expr instanceof Func) {
            $vars = self::freeVariables($expr->getBody());
            $vars->remove($expr->getName());
        }
        return vars;
    }
}
