<?php

namespace App\Core\CekMachine\LambdaCalculus;

abstract class Expression
{
    public abstract function reducible() : bool;

    public abstract function reduce() : self;

    public abstract function deepReduce() : self;
}
