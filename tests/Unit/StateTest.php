<?php

namespace Tests\Unit;

use App\Core\Automata\State;
use Tests\TestCase;

class StateTest extends TestCase
{
    public function testId() : void
    {
        $state = new State(3);
        $this->assertEquals(3, $state->getId());
    }

    public function testData() : void
    {
        $state = new State(0, ['some', 'attached', 'data']);
        $this->assertEquals(['some', 'attached', 'data'], $state->getData());
    }

    public function testFinal() : void
    {
        $state = new State(0);
        $this->assertFalse($state->isFinal());

        $state->setFinal();
        $this->assertTrue($state->isFinal());
    }

    public function testNoConnectedStates() : void
    {
        $state = new State(0);
        $this->assertEquals([], $state->getConnectedStates());
    }

    public function testAddTransition() : State
    {
        $s1 = new State(0);
        $s2 = new State(1);

        $s1->addTransition($s2, 'a');

        $this->assertEquals(['a' => [$s2]], $s1->getConnectedStates());

        return $s1;
    }

    public function testNoTransition() : void
    {
        $s = new State(0);

        $this->assertFalse($s->hasTransition('a'));
    }

    /**
     * @depends testAddTransition
     */
    public function testHasTransition(State $s) : void
    {
        $this->assertTrue($s->hasTransition('a'));
    }

    public function testRemoveTransition() : void
    {
        $s0 = new State(0);
        $s1 = new State(1);
        $s2 = new State(2);

        $s0->addTransition($s1, 'a');
        $s0->addTransition($s2, 'a');

        $this->assertEquals(['a' => [$s1, $s2]], $s0->getConnectedStates());

        $s0->removeTransition($s1, 'a');

        $this->assertEquals(['a' => [$s2]], $s0->getConnectedStates());
    }

    public function testGetChars() : void
    {
        $s0 = new State(0);
        $alphabet = [];

        for ($i = 65; $i <= 90; $i++) {
            $alphabet[] = chr($i);
            $s0->addTransition(new State(), chr($i));
        }

        $this->assertEquals($alphabet, $s0->getChars());
    }
}
