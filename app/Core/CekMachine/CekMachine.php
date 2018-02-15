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
        $steps = [];
        $current = ['data' => $this->jsonSerialize()];

        while (true) {
            $action = $this->nextStep();

            $current['label'] = $action;

            $steps[] = $current;

            $current['label'] = null;
            $current['data'] = $this->jsonSerialize();

            if ($this->control instanceof Value && $this->continuation->isEmpty()) {
                $steps[] = $current;
                break;
            }
        }

        return $steps;
    }

    public function nextStep()
    {
        $control = clone $this->control;
        $action = null;

        if ($control instanceof Variable) {

            $action = 'VARIABLE';

            $value = $this->environment->lookup($control);

            if ($value === null) {
                throw new CekMachineException('No variable in the environment.');
            }

            $this->control = $value;

        } else if ($control instanceof Application) {

            $action = 'APPLICATION';

            $frame = new Frame(new Pair(null, new Pair(
                $this->control->getArgument(),
                clone $this->environment
            )));

            $this->continuation->push($frame);
            $this->control = $control->getFunction();

        } else if ($control instanceof Func) {

            $action = 'FUNCTION';

            $this->control = new Closure($control, clone $this->environment);

        } else if ($control instanceof Value) {

            $frame = $this->continuation->peek();
            $frameContent = clone $frame->getContent();

            if ($frameContent->getFst() === null) {
                $action = 'VALUE_1';

                $this->environment = $frameContent->getSnd()->getSnd();
                $this->continuation->pop();
                $this->continuation->push(new Frame(new Pair($control, null)));
                $this->control = $frameContent->getSnd()->getFst();
            } else if ($frameContent->getFst() instanceof Closure) {
                $action = 'VALUE_2';

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

        return $action;
    }

    public function jsonSerialize()
    {
        return [
            'control'      => $this->control,
            'environment'  => $this->environment->jsonSerialize(),
            'continuation' => $this->continuation->jsonSerialize()
        ];
    }
}
