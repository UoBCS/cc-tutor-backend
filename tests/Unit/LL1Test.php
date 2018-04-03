<?php

namespace Tests\Unit;

use App\Core\Lexer\Lexer;
use App\Core\Parser\LL1;
use Tests\TestCase;

class LL1Test extends TestCase
{
    /**
     * @dataProvider parseDataProvider
     */
    public function testLL1Parse($parserData, $expectedParseTree) : void
    {
        $lexer = new Lexer($parserData['content'], $parserData['token_types']);
        $parser = new LL1($lexer, $parserData['grammar']);
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
