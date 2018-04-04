<?php

namespace Tests\Unit;

use App\Core\Syntax\Regex\RegexParser;
use Tests\TestCase;

class RegexParserTest extends TestCase
{
    /**
     * @dataProvider regexParseDataProvider
     */
    public function testRegexParse($regex, $expected) : void
    {
        $parser = new RegexParser($regex);

        $this->assertEquals($expected, json_decode(json_encode($parser->parse()), true));
    }

    public function regexParseDataProvider() : array
    {
        $tree1 = [
            'name' => 'SEQ',
            'children' => [
                [
                    'name' => 'SEQ',
                    'children' => [
                        ['name' => 'a'],
                        ['name' => 'b']
                    ]
                ],
                ['name' => 'c']
            ]
        ];

        $tree2 = [
            'name' => 'OR',
            'children' => [
                [
                    'name' => 'SEQ',
                    'children' => [
                        ['name' => 'a'],
                        ['name' => 'b']
                    ]
                ],
                ['name' => 'ε']
            ]
        ];

        $tree3 = [
            'name' => 'OR',
            'children' => [
                ['name' => 'a'],
                ['name' => 'b']
            ]
        ];

        $tree4 = [
            'name' => 'SEQ',
            'children' => [
                ['name' => '[ANY]'],
                [
                    'name'     => 'REP',
                    'children' => [['name' => '[ANY]']]
                ]
            ]
        ];

        $tree5 = [
            'name' => 'REP',
            'children' => [
                [
                    'name' => 'OR',
                    'children' => [
                        ['name' => '[0-9]'],
                        ['name' => '[a-z]'],
                        ['name' => '[A-z]']
                    ]
                ]
            ]
        ];

        return [
            ['abc', $tree1],
            ['(ab)?', $tree2],
            ['a|b', $tree3],
            ['.+', $tree4],
            ['[0-9a-zA-z]*', $tree5]
        ];
    }
}
