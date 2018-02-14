<?php

namespace App\Core\CekMachine;

use App\Core\CekMachine\LambdaCalculus\Application;
use App\Core\CekMachine\LambdaCalculus\Expression;
use App\Core\CekMachine\LambdaCalculus\Func;
use App\Core\CekMachine\LambdaCalculus\Lambda;
use App\Core\CekMachine\LambdaCalculus\Variable;
use App\Core\Exceptions\CekMachineException;
use App\Infrastructure\Utils\Ds\Pair;
use Ds\Stack;
use JsonSerializable;

class CekMachine implements JsonSerializable
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

    public static function fromJson(array $data) : self
    {
        $control = null;

        if (is_string($data['control'])) {
            $control = Lambda::parse($data['control']);
        } else {
            $control = isset($data['control']['type'])
                        ? Expression::fromJson($data['control'])
                        : Closure::fromJson($data['control']);
        }

        $environment = Environment::fromJson($data['environment']);

        $continuation = new Stack();

        for ($i = count($data['continuation']) - 1; $i >= 0; $i--) {
            $continuation->push(Frame::fromJson($data['continuation'][$i]));
        }

        return new CekMachine($control, $environment, $continuation);
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

    public function run()
    {
        $steps = [$this->jsonSerialize()];

        while (true) {
            $this->nextStep();

            $steps[] = $this->jsonSerialize();

            if ($this->control instanceof Value && $this->continuation->isEmpty()) {
                break;
            }
        }

        return $steps;
    }

    public function nextStep()
    {
        $control = clone $this->control;

        if ($control instanceof Variable) {

            $value = $this->environment->lookup($control);

            if ($value === null) {
                throw new CekMachineException('No variable in the environment.');
            }

            $this->control = $value;

        } else if ($control instanceof Application) {

            $frame = new Frame(new Pair(null, new Pair(
                $this->control->getArgument(),
                clone $this->environment
            )));

            $this->continuation->push($frame);
            $this->control = $control->getFunction();

        } else if ($control instanceof Func) {

            $this->control = new Closure($control, clone $this->environment);

        } else if ($control instanceof Value) {

            $frame = $this->continuation->peek();
            $frameContent = clone $frame->getContent();

            if ($frameContent->getFst() === null) {
                $this->environment = $frameContent->getSnd()->getSnd();
                $this->continuation->pop();
                $this->continuation->push(new Frame(new Pair($control, null)));
                $this->control = $frameContent->getSnd()->getFst();
            } else if ($frameContent->getFst() instanceof Closure) {
                $function = $frameContent->getFst()->getFunction();
                $environment = $frameContent->getFst()->getEnvironment();
                $environment->update($function->getName(), $control);

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
        return [
            'control'      => $this->control->jsonSerialize(),
            'environment'  => $this->environment->jsonSerialize(),
            'continuation' => $this->continuation->jsonSerialize()
        ];
    }
}
