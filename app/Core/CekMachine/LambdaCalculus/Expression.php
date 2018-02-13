<?php

namespace App\Core\CekMachine\LambdaCalculus;

use App\Core\Exceptions\CekMachineException;

abstract class Expression
{
    public abstract function reducible() : bool;

    public abstract function reduce() : self;

    public abstract function deepReduce() : self;

    public static function fromJson(array $data) : self
    {
        switch ($data['type']) {

            case 'VAR': return new Variable($data['name']);

            case 'CONST': return new Constant(self::fromJson($data['value']));

            case 'FUNC': return new Func(self::fromJson($data['name']), self::fromJson($data['body']));

            case 'APPL': return new Application(self::fromJson($data['function']), self::fromJson($data['argument']));

            default: throw new CekMachineException('Not a valid data type.');

        }
    }
}
