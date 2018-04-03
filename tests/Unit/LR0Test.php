<?php

namespace Tests\Unit;

use App\Core\Lexer\Lexer;
use App\Core\Parser\LR0;
use Tests\TestCase;

class LR0Test extends TestCase
{
    /**
     * @dataProvider parseDataProvider
     */
    public function testLR0Parse($parserData, $expectedParseTree) : void
    {
        $lexer = new Lexer($parserData['content'], $parserData['token_types']);
        $parser = new LR0($lexer, $parserData['grammar']);
        $parser->parse();

        $this->assertEquals($parser->getJsonParseTree(), $expectedParseTree);
    }

    public function parseDataProvider() : array
    {
        $parserData1 = [
            'content' => 'aab',
            'token_types' => [
                ['name' => 'A', 'regex' => 'a', 'skippable' => false, 'priority' => 0],
                ['name' => 'B', 'regex' => 'b', 'skippable' => false, 'priority' => 0],
            ],
            'grammar' => [
                'productions' => [
                    's' => [['l', 'B']],
                    'l' => [['A', 'l'], null]
                ],
                'start_symbol' => 's'
            ]
        ];

        $expectedParseTree1 = [
            'node' => 's',
            'children' => [
                [
                    'node' => 'B',
                    'children' => []
                ],
                [
                    'node' => 'l',
                    'children' => [
                        [
                            'node' => 'l',
                            'children' => [
                                [
                                    'node' => 'l',
                                    'children' => []
                                ],
                                [
                                    'node' => 'A',
                                    'children' => []
                                ]
                            ]
                        ],
                        [
                            'node' => 'A',
                            'children' => []
                        ]
                    ]
                ]
            ]
        ];

        return [
            [$parserData1, $expectedParseTree1]
        ];
    }
}
