<?php

namespace Tests\Unit;

use App\Core\Automata\FiniteAutomaton;
use App\Core\Automata\State;
use App\Core\Syntax\Regex\PlainRegex;
use Tests\TestCase;

class FiniteAutomatonTest extends TestCase
{
    /**
     * @dataProvider fromRegexDataProvider
     */
    public function testFromRegex($regex, $expected) : void
    {
        $this->assertEquals($expected, FiniteAutomaton::fromRegex($regex, false)->toArray());
    }

    public function testCombination() : void
    {
        $fa1 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => true],
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1]
            ]
        ];
        $fa2 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => true],
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'b', 'dest' => 1]
            ]
        ];
        $expected = [
            'states' => [
                ['id' => 0, 'final' => false, 'data' => []],
                ['id' => 1, 'final' => false, 'data' => []],
                ['id' => 2, 'final' => true, 'data' => []],
                ['id' => 3, 'final' => false, 'data' => []],
                ['id' => 4, 'final' => true, 'data' => []],
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'ε', 'dest' => 3],
                ['src' => 0, 'char' => 'ε', 'dest' => 1],
                ['src' => 1, 'char' => 'b', 'dest' => 2],
                ['src' => 3, 'char' => 'a', 'dest' => 4],
            ]
        ];

        $this->assertEquals($expected, FiniteAutomaton::combine([FiniteAutomaton::fromArray($fa1), FiniteAutomaton::fromArray($fa2)])->toArray());
    }

    public function testGenerateAlphabet() : void
    {
        $s0 = new State(0);
        $alphabet = [];

        for ($i = 65; $i <= 90; $i++) {
            $alphabet[] = chr($i);
            $s0->addTransition(new State(), chr($i));
        }

        $fa = new FiniteAutomaton($s0);

        $this->assertEquals($alphabet, $fa->generateAlphabet()->toArray());
    }

    /**
     * @dataProvider determinismCheckDataProvider
     */
    public function testDeterminismCheck($automaton, $expected) : void
    {
        $this->assertEquals($expected, FiniteAutomaton::fromArray($automaton)->isDeterministic());
    }

    /**
     * @dataProvider setIdsDataProvider
     */
    public function testSetIds($automaton, $expected) : void
    {
        $fa = FiniteAutomaton::fromArray($automaton);
        $fa->setIds();

        $this->assertEquals($expected, $fa->toArray());
    }

    /**
     * @dataProvider convertToDfaDataProvider
     */
    public function testConvertToDfa($nfa, $expectedDfa) : void
    {
        $this->assertEquals($expectedDfa, $this->normalizeData(
            FiniteAutomaton::fromArray($nfa)->toDfa()->toArray()
        ));
    }

    /**
     * @dataProvider acceptsDataProvider
     */
    public function testAccepts($dfa, $word, $expected) : void
    {
        $this->assertEquals($expected, FiniteAutomaton::fromArray($dfa)->accepts($word));
    }

    /*public function testMinimizeDfa() : void
    {

    }*/

    public function acceptsDataProvider() : array
    {
        $dfa1 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => false],
                ['id' => 2, 'final' => true],
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1],
                ['src' => 1, 'char' => 'b', 'dest' => 2],
            ]
        ];

        return [
            [$dfa1, 'ab', true]
        ];
    }

    public function convertToDfaDataProvider() : array
    {
        $nfa1 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => false],
                ['id' => 2, 'final' => true],
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'ε', 'dest' => 1],
                ['src' => 1, 'char' => 'a', 'dest' => 2]
            ]
        ];

        $dfa1 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => true]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1]
            ]
        ];

        $nfa2 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => true],
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1]
            ]
        ];

        $nfa3 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => false],
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 0],
                ['src' => 0, 'char' => 'a', 'dest' => 1],
            ]
        ];

        $dfa3 = [
            'states' => [
                ['id' => 0, 'final' => false]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 0]
            ]
        ];

        return [
            [$nfa1, $dfa1],
            [$nfa2, $nfa2],
            [$nfa3, $dfa3]
        ];
    }

    public function setIdsDataProvider() : array
    {
        $a1 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 43, 'final' => false],
                ['id' => 246, 'final' => false],
                ['id' => 3312, 'final' => false]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 43],
                ['src' => 43, 'char' => 'b', 'dest' => 246],
                ['src' => 246, 'char' => 'c', 'dest' => 3312]
            ]
        ];

        return [
            [$a1, [
                'states' => [
                    ['id' => 0, 'final' => false, 'data' => []],
                    ['id' => 1, 'final' => false, 'data' => []],
                    ['id' => 2, 'final' => false, 'data' => []],
                    ['id' => 3, 'final' => false, 'data' => []]
                ],
                'transitions' => [
                    ['src' => 0, 'char' => 'a', 'dest' => 1],
                    ['src' => 1, 'char' => 'b', 'dest' => 2],
                    ['src' => 2, 'char' => 'c', 'dest' => 3]
                ]
            ]],
        ];
    }

    public function determinismCheckDataProvider() : array
    {
        $a1 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => false],
                ['id' => 2, 'final' => false]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1],
                ['src' => 0, 'char' => 'b', 'dest' => 2]
            ]
        ];

        $a2 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => false],
                ['id' => 2, 'final' => false]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1],
                ['src' => 0, 'char' => 'ε', 'dest' => 2]
            ]
        ];

        $a3 = [
            'states' => [
                ['id' => 0, 'final' => false],
                ['id' => 1, 'final' => false],
                ['id' => 2, 'final' => false]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1],
                ['src' => 0, 'char' => 'a', 'dest' => 2]
            ]
        ];

        return [
            [$a1, true],
            [$a2, false],
            [$a3, false]
        ];
    }

    public function fromRegexDataProvider() : array
    {
        $a1 = [
            'states' => [
                ['id' => 0, 'final' => false, 'data' => []],
                ['id' => 1, 'final' => false, 'data' => []],
                ['id' => 2, 'final' => false, 'data' => []],
                ['id' => 3, 'final' => true, 'data' => []]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1],
                ['src' => 1, 'char' => 'ε', 'dest' => 2],
                ['src' => 2, 'char' => 'b', 'dest' => 3],
            ]
        ];

        $a2 = [
            'states' => [
                ['id' => 0, 'final' => false, 'data' => []],
                ['id' => 1, 'final' => true, 'data' => []],
            ],
            'transitions' => [
                ['src' => 0, 'char' => '[0-9]', 'dest' => 1]
            ]
        ];

        $a3 = [
            'states' => [
                ['id' => 0, 'final' => false, 'data' => []],
                ['id' => 1, 'final' => true, 'data' => []],
                ['id' => 2, 'final' => false, 'data' => []],
                ['id' => 3, 'final' => false, 'data' => []]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 2],
                ['src' => 0, 'char' => 'ε', 'dest' => 1],
                ['src' => 1, 'char' => 'ε', 'dest' => 0],
                ['src' => 2, 'char' => 'ε', 'dest' => 3],
                ['src' => 3, 'char' => 'b', 'dest' => 1]
            ]
        ];

        $a4 = [
            'states' => [
                ['id' => 0, 'final' => false, 'data' => []],
                ['id' => 1, 'final' => false, 'data' => []],
                ['id' => 2, 'final' => false, 'data' => []],
                ['id' => 3, 'final' => true, 'data' => []]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'a', 'dest' => 1],
                ['src' => 1, 'char' => 'ε', 'dest' => 2],
                ['src' => 2, 'char' => 'a', 'dest' => 3],
                ['src' => 2, 'char' => 'ε', 'dest' => 3],
                ['src' => 3, 'char' => 'ε', 'dest' => 2]
            ]
        ];

        $a5 = [
            'states' => [
                ['id' => 0, 'final' => false, 'data' => []],
                ['id' => 1, 'final' => false, 'data' => []],
                ['id' => 2, 'final' => false, 'data' => []],
                ['id' => 3, 'final' => true, 'data' => []],
                ['id' => 4, 'final' => false, 'data' => []],
                ['id' => 5, 'final' => false, 'data' => []]
            ],
            'transitions' => [
                ['src' => 0, 'char' => 'ε', 'dest' => 4],
                ['src' => 0, 'char' => 'ε', 'dest' => 1],
                ['src' => 1, 'char' => 'b', 'dest' => 2],
                ['src' => 2, 'char' => 'ε', 'dest' => 3],
                ['src' => 4, 'char' => 'a', 'dest' => 5],
                ['src' => 5, 'char' => 'ε', 'dest' => 3]
            ]
        ];

        return [
            [new PlainRegex('ab'), $a1],
            [new PlainRegex('[0-9]'), $a2],
            [new PlainRegex('(ab)*'), $a3],
            [new PlainRegex('a+'), $a4],
            [new PlainRegex('a|b'), $a5],
        ];
    }

    private function normalizeData($data)
    {
        $states = [];

        foreach ($data['states'] as $state) {
            $states[] = [
                'id' => $state['id'],
                'final' => $state['final']
            ];
        }

        return [
            'states' => $states,
            'transitions' => $data['transitions']
        ];
    }
}
