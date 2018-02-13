<?php

namespace App\Core\CekMachine;

use App\Core\Exceptions\CekMachineException;
use App\Infrastructure\Utils\Ds\Pair;
use LambdaCalculus\Application;
use LambdaCalculus\Variable;
use Ds\Stack;
use JsonSerializable;

class CekMachine implements JsonSerializble
{
    private $control;
    private $environment;
    private $continuation;

    public function __construct($control, Environment $environment, Stack $continuation)
    {
        $this->control = $control;
        $this->environment = $environment;
        $this->continuation = $continuation;
    }

    public function getControl()
    {
        return $this->control;
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    public function getContinuation() : Stack
    {
        return $this->continuation;
    }

    public function nextStep()
    {
        if ($this->control instanceof Variable) {

            $value = $this->environment->lookup($this->control);

            if ($value === null) {
                throw new CekMachineException('No variable in the environment.');
            }

            $this->control = $value;

        } else if ($this->control instanceof Application) {

            $frame = new Frame(new Pair(null, new Pair(
                clone $this->control->getArgument(),
                clone $this->environment
            )));

            $this->continuation->push($frame);
            $this->control = $this->control->getFunction();

        } else if ($this->control instanceof Func) {

            $this->control = new Closure(clone $this->control, clone $this->environment);

        } else if ($this->control instanceof Value) {

            $frame = $this->continuation->peek();

            if ($frame->getFst() === null) {
                $this->environment = clone $frame->getSnd()->getSnd();
                $this->continuation->pop();
                $this->continuation->push(new Frame(new Pair(clone $this->control, null)));
            } else if ($frame->getFst() instanceof Closure) {
                $function = $frame->getFst()->getFunction();
                $environment = $frame->getFst()->getEnvironment();
                $environment->update($function->getName(), clone $this->control);

                $this->control = $function->getBody();
                $this->environment = $environment;
                $this->continuation->pop();
            } else {
                throw new CekMachineException('Not a valid closure.');
            }

        }
    }

    public function jsonSerialize()
    {
        return null;
    }
}
