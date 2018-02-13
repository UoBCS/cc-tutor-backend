<?php

namespace App\Core\CekMachine\LambdaCalculus;

class Lambda
{
    public static function var(string $name) : Variable
    {
        return new Variable($name);
    }

    public static function lambda($name, $body) : Func
    {
        return new Func(
            $name instanceof Variable ? $name : new Variable($name),
            $body instanceof Expression ? $body : new Variable($body)
        );
    }

    public static function λ($name, $body) : Func
    {
        return self::lambda($name, $body);
    }

    public static function apply($function, $argument) : Application
    {
        return new Application(
            $function instanceof Expression ? $function : new Variable($function),
            $argument instanceof Expression ? $argument : new Variable($argument)
        );
    }
}
