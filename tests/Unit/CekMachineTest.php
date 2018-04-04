<?php

namespace Tests\Unit;

use App\Core\CekMachine\CekMachine;
use App\Core\CekMachine\LambdaCalculus\Lambda;
use Tests\TestCase;

class CekMachineTest extends TestCase
{
    /**
     * @dataProvider nextStepDataProvider
     */
    public function testNextStep($data, $expected) : void
    {
        $cekMachine = CekMachine::fromJson($data);
        $cekMachine->nextStep();

        $actual = json_decode(json_encode($cekMachine), true);
        $expected['control'] = json_decode(json_encode(Lambda::parse($expected['control'])), true);

        $this->assertEquals($expected, $actual);
    }

    public function nextStepDataProvider() : array
    {
        $input1 = [
            'control' => '(\x.x)1',
            'environment' => [],
            'continuation' => []
        ];

        $output1 = [
            'control' => '\x.x',
            'environment' => [],
            'continuation' => [
                [null, [['type' => 'CONST', 'value' => 1], []]]
            ]
        ];

        return [
            [$input1, $output1]
        ];
    }
}
